<?php
require_once __DIR__ . '/../database/Conexion.php';

class OrdenModel {
    
    public static function crearOrden($id_usuario, $cliente, $horaEntrega, $observaciones, $estadoPago, $idMetodoPago, $items) {
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // 1. PRECIOS SIEMPRE DESDE LA BASE DE DATOS.
            //    Del navegador solo se usan los IDs (producto y extras); cualquier precio que
            //    llegue en el JSON se ignora, así nadie puede alterar un total desde las
            //    herramientas del navegador. También se rechazan productos/extras inactivos.
            $stmtProducto = $db->prepare("SELECT id_producto, nombre, precio FROM productos WHERE id_producto = ? AND activo = 1");
            // Regla del negocio: solo las PIZZAS llevan ingredientes extra (la misma que usa el POS
            // en CatalogoModel::getExtrasPorProducto): categoría 1 o categoría con "pizza" en el nombre.
            $stmtExtraValido = $db->prepare("
                SELECT e.precio
                FROM extras e
                JOIN productos p ON p.id_producto = ?
                JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE e.id_extra = ? AND e.activo = 1
                  AND (c.id_categoria = 1 OR c.nombre LIKE '%pizza%')
            ");

            $lineas = [];
            $totalOrden = 0.0;
            foreach ((array)$items as $item) {
                $item = is_array($item) ? $item : [];
                $id_producto = intval($item['idProducto'] ?? 0);

                $stmtProducto->execute([$id_producto]);
                $producto = $stmtProducto->fetch();
                if (!$producto) {
                    throw new Exception("Un producto de la orden ya no está disponible. Recarga el POS (F5) e inténtalo de nuevo.");
                }

                $precioUnitario = (float)$producto['precio'];
                $extrasValidos = []; // id_extra => precio real
                foreach ((array)($item['extras'] ?? []) as $extra) {
                    $id_extra = intval(is_array($extra) ? ($extra['id'] ?? 0) : 0);
                    if ($id_extra <= 0 || isset($extrasValidos[$id_extra])) continue;

                    $stmtExtraValido->execute([$id_producto, $id_extra]);
                    $filaExtra = $stmtExtraValido->fetch();
                    if (!$filaExtra) {
                        throw new Exception("Un extra seleccionado no es válido para «{$producto['nombre']}». Recarga el POS (F5).");
                    }
                    $extrasValidos[$id_extra] = (float)$filaExtra['precio'];
                    $precioUnitario += (float)$filaExtra['precio'];
                }

                $precioUnitario = round($precioUnitario, 2);
                $lineas[] = ['id_producto' => $id_producto, 'precio' => $precioUnitario, 'extras' => $extrasValidos];
                $totalOrden += $precioUnitario;
            }

            if (empty($lineas)) {
                throw new Exception("La orden no contiene productos.");
            }
            $totalOrden = round($totalOrden, 2);

            // 2. Crear registro en la tabla 'ordenes'
            $stmt = $db->prepare("
                INSERT INTO ordenes (codigo, id_usuario, cliente, hora_entrega, observaciones, id_estado, subtotal, descuento, total) 
                VALUES ('TEMP', ?, ?, ?, ?, 1, ?, 0, ?)
            ");
            $stmt->execute([$id_usuario, $cliente, $horaEntrega, $observaciones, $totalOrden, $totalOrden]);
            $id_orden = $db->lastInsertId();

            // 3. Generar código único correlativo (ej. ORD-0021)
            $codigo = 'ORD-' . str_pad($id_orden, 4, '0', STR_PAD_LEFT);
            $stmtCodigo = $db->prepare("UPDATE ordenes SET codigo = ? WHERE id_orden = ?");
            $stmtCodigo->execute([$codigo, $id_orden]);

            // 4. Insertar cada producto en 'detalle_orden' y sus extras en 'detalle_orden_extra'
            $stmtDetalle = $db->prepare("
                INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario) 
                VALUES (?, ?, 1, ?)
            ");
            $stmtExtra = $db->prepare("
                INSERT INTO detalle_orden_extra (id_detalle, id_extra, precio) 
                VALUES (?, ?, ?)
            ");
            foreach ($lineas as $linea) {
                $stmtDetalle->execute([$id_orden, $linea['id_producto'], $linea['precio']]);
                $id_detalle = $db->lastInsertId();
                foreach ($linea['extras'] as $id_extra => $precioExtra) {
                    $stmtExtra->execute([$id_detalle, $id_extra, $precioExtra]);
                }
            }

            // 5. Registrar Pago si la orden fue marcada como "Pagada"
            if ($estadoPago === 'pagado') {
                $stmtPago = $db->prepare("
                    INSERT INTO pagos (id_orden, id_metodo_pago, id_usuario, monto) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmtPago->execute([$id_orden, $idMetodoPago, $id_usuario, $totalOrden]);
            }

            $db->commit();
            return ['success' => true, 'codigo' => $codigo, 'id_orden' => $id_orden];

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Arma los datos del TICKET de una orden leyendo SOLO lo guardado en la base de datos
     * (lo que se imprime es siempre lo que quedó registrado, no lo que mandó el navegador).
     * Devuelve null si la orden no existe.
     */
    public static function getTicket($id_orden) {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT o.id_orden, o.codigo, o.cliente, o.hora_entrega, o.observaciones,
                   o.fecha_creacion, o.subtotal, o.descuento, o.total,
                   e.nombre AS estado,
                   CONCAT(u.nombre, ' ', u.apellido) AS cajero
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
            WHERE o.id_orden = ?
        ");
        $stmt->execute([$id_orden]);
        $o = $stmt->fetch();
        if (!$o) return null;

        // Productos de la orden
        $stmtItems = $db->prepare("
            SELECT d.id_detalle, p.nombre, d.cantidad, d.precio_unitario, d.subtotal
            FROM detalle_orden d
            JOIN productos p ON p.id_producto = d.id_producto
            WHERE d.id_orden = ?
            ORDER BY d.id_detalle
        ");
        $stmtItems->execute([$id_orden]);

        $stmtExtras = $db->prepare("
            SELECT x.nombre, dx.precio
            FROM detalle_orden_extra dx
            JOIN extras x ON x.id_extra = dx.id_extra
            WHERE dx.id_detalle = ?
            ORDER BY x.nombre
        ");

        $items = [];
        foreach ($stmtItems->fetchAll() as $fila) {
            $stmtExtras->execute([$fila['id_detalle']]);
            $extras = [];
            $sumaExtras = 0.0;
            foreach ($stmtExtras->fetchAll() as $ex) {
                $extras[] = ['nombre' => $ex['nombre'], 'precio' => (float)$ex['precio']];
                $sumaExtras += (float)$ex['precio'];
            }
            $items[] = [
                'cantidad'        => intval($fila['cantidad']),
                'nombre'          => $fila['nombre'],
                'precio_base'     => round((float)$fila['precio_unitario'] - $sumaExtras, 2),
                'extras'          => $extras,
                'precio_unitario' => (float)$fila['precio_unitario'],
                'subtotal'        => (float)$fila['subtotal'],
            ];
        }

        // Pagos registrados
        $stmtPagos = $db->prepare("
            SELECT m.nombre AS metodo, pg.monto, pg.fecha_pago
            FROM pagos pg
            JOIN metodos_pago m ON m.id_metodo_pago = pg.id_metodo_pago
            WHERE pg.id_orden = ?
            ORDER BY pg.id_pago
        ");
        $stmtPagos->execute([$id_orden]);
        $pagos = $stmtPagos->fetchAll();
        $montoPagado = 0.0;
        foreach ($pagos as $pg) { $montoPagado += (float)$pg['monto']; }

        $pagado = !empty($pagos);
        return [
            'codigo'        => $o['codigo'],
            'fecha'         => date('d/m/Y h:i A', strtotime($o['fecha_creacion'])),
            'cajero'        => trim($o['cajero'] ?? ''),
            'cliente'       => $o['cliente'] ?: 'Cliente Mostrador',
            'hora_entrega'  => $o['hora_entrega'] ? date('h:i A', strtotime($o['hora_entrega'])) : '',
            'observaciones' => $o['observaciones'] ?? '',
            'estado_orden'  => $o['estado'],
            'items'         => $items,
            'subtotal'      => (float)$o['subtotal'],
            'descuento'     => (float)$o['descuento'],
            'total'         => (float)$o['total'],
            'pagado'        => $pagado,
            'metodo_pago'   => $pagado ? $pagos[0]['metodo'] : '',
            'monto_pagado'  => round($montoPagado, 2),
            'fecha_pago'    => $pagado ? date('d/m/Y h:i A', strtotime($pagos[0]['fecha_pago'])) : '',
        ];
    }

    /**
     * Órdenes para la pantalla de Entregas del cajero.
     *  - ACTIVAS (1 Pendiente, 2 En preparación, 3 Lista): de CUALQUIER día, así las
     *    atrasadas no se pierden y se pueden terminar de procesar.
     *  - ENTREGADAS (4): solo las despachadas hoy (historial del día).
     * es_atrasada = 1 cuando la orden se creó en un día anterior a hoy.
     */
    public static function getOrdenesParaEntrega() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT o.id_orden,
                   o.codigo,
                   o.total,
                   e.nombre AS estado_nombre,
                   o.id_estado,
                   o.fecha_creacion,
                   o.fecha_actualizacion,
                   o.hora_entrega,
                   o.id_usuario,
                   IF(DATE(o.fecha_creacion) < CURDATE(), 1, 0) AS es_atrasada,
                   COALESCE(o.cliente, u.nombre, 'Cliente Mostrador') AS cliente,
                   IF((SELECT COUNT(*) FROM pagos pg WHERE pg.id_orden = o.id_orden) > 0, 1, 0) AS esta_pagado,
                   (
                       SELECT JSON_ARRAYAGG(
                           JSON_OBJECT('nombre', p.nombre, 'cantidad', d.cantidad)
                       )
                       FROM detalle_orden d
                       JOIN productos p ON p.id_producto = d.id_producto
                       WHERE d.id_orden = o.id_orden
                   ) AS detalles_json
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
            WHERE o.id_estado IN (1, 2, 3)
               OR (o.id_estado = 4 AND DATE(o.fecha_actualizacion) = CURDATE())
            ORDER BY
                CASE WHEN o.id_estado = 4 THEN 2 ELSE 1 END,
                es_atrasada DESC,
                o.fecha_actualizacion DESC
        ");
        $ordenes = $stmt->fetchAll();

        // La vista trabaja con un array 'detalles', no con el JSON crudo de MySQL
        foreach ($ordenes as &$orden) {
            $detalles = json_decode($orden['detalles_json'] ?? '[]', true);
            $orden['detalles'] = is_array($detalles) ? $detalles : [];
        }
        unset($orden);

        return $ordenes;
    }

    /**
     * Cuenta las órdenes SIN ENTREGAR (estados 1, 2 y 3) creadas por una caja (usuario),
     * de cualquier día. Es lo que impide cerrar ESA caja: cada caja responde por sus órdenes,
     * así una caja puede cerrar aunque la otra siga atendiendo.
     */
    public static function contarOrdenesSinEntregar($id_usuario) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM ordenes WHERE id_usuario = ? AND id_estado IN (1, 2, 3)");
        $stmt->execute([$id_usuario]);
        return intval($stmt->fetchColumn());
    }

    // Cambiar estado a Entregada (id_estado = 4)
    public static function marcarComoEntregada($id_orden) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ordenes SET id_estado = 4 WHERE id_orden = ?");
        return $stmt->execute([$id_orden]);
    }

    
// Procesar la entrega y registrar el pago si estaba pendiente
    public static function procesarEntregaYCobro($id_orden, $idMetodoPago, $id_usuario) {
            $db = Database::getConnection();
            
            try {
                $db->beginTransaction();

                // 1. Obtener la orden
                $stmt = $db->prepare("SELECT total, id_estado FROM ordenes WHERE id_orden = ? FOR UPDATE");
                $stmt->execute([$id_orden]);
                $orden = $stmt->fetch();

                if (!$orden) {
                    throw new Exception("La orden especificada no existe.");
                }

                // Solo se entrega lo que cocina dejó 'Lista' (evita entregar dos veces o una cancelada)
                if (intval($orden['id_estado']) !== 3) {
                    throw new Exception("La orden aún no está lista en cocina o ya fue entregada.");
                }

                // 2. Verificar si ya se registró el pago previamente
                $stmtPago = $db->prepare("SELECT COUNT(*) FROM pagos WHERE id_orden = ?");
                $stmtPago->execute([$id_orden]);
                $tienePago = $stmtPago->fetchColumn() > 0;

                // 3. Registrar el pago si estaba pendiente
                if (!$tienePago) {
                    $stmtInsertPago = $db->prepare("
                        INSERT INTO pagos (id_orden, id_metodo_pago, id_usuario, monto) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmtInsertPago->execute([$id_orden, $idMetodoPago, $id_usuario, $orden['total']]);
                }

                // 4. Cambiar el estado a 'Entregada' (Estado 4)
                $stmtUpdate = $db->prepare("UPDATE ordenes SET id_estado = 4, fecha_actualizacion = NOW() WHERE id_orden = ?");
                $stmtUpdate->execute([$id_orden]);

                $db->commit();
                return ['success' => true];

            } catch (Exception $e) {
                $db->rollBack();
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }

}
?>