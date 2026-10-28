<?php
require_once __DIR__ . '/../database/conexion.php';

class OrdenModel {
    
    public static function crearOrden($id_usuario, $estadoPago, $items) {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            // 1. Crear registro en la tabla 'ordenes'
            // id_estado = 1 (Pendiente por defecto para cocina)
            $stmt = $db->prepare("
                INSERT INTO ordenes (codigo, id_usuario, id_estado, origen, subtotal, descuento, total) 
                VALUES ('TEMP', ?, 1, 'Mostrador', 0, 0, 0)
            ");
            $stmt->execute([$id_usuario]);
            $id_orden = $db->lastInsertId();

            // 2. Generar código único correlativo (ej. ORD-0001)
            $codigo = 'ORD-' . str_pad($id_orden, 4, '0', STR_PAD_LEFT);
            $stmtCodigo = $db->prepare("UPDATE ordenes SET codigo = ? WHERE id_orden = ?");
            $stmtCodigo->execute([$codigo, $id_orden]);

            $totalOrden = 0;

            // 3. Insertar cada producto en 'detalle_orden'
            foreach ($items as $item) {
                $id_producto = $item['idProducto'];
                $precioUnitario = $item['precioUnitario'];
                $totalOrden += $precioUnitario;

                $stmtDetalle = $db->prepare("
                    INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario) 
                    VALUES (?, ?, 1, ?)
                ");
                $stmtDetalle->execute([$id_orden, $id_producto, $precioUnitario]);
                $id_detalle = $db->lastInsertId();

                // 4. Insertar extras seleccionados en 'detalle_orden_extra'
                if (!empty($item['extras'])) {
                    foreach ($item['extras'] as $extra) {
                        $stmtExtra = $db->prepare("
                            INSERT INTO detalle_orden_extra (id_detalle, id_extra, precio) 
                            VALUES (?, ?, ?)
                        ");
                        $stmtExtra->execute([$id_detalle, $extra['id'], $extra['precio']]);
                    }
                }
            }

            // 5. Registrar Pago si la orden fue marcada como "Pagada"
            if ($estadoPago === 'pagado') {
                // Método de pago 1 = Efectivo
                $stmtPago = $db->prepare("
                    INSERT INTO pagos (id_orden, id_metodo_pago, id_usuario, monto) 
                    VALUES (?, 1, ?, ?)
                ");
                $stmtPago->execute([$id_orden, $id_usuario, $totalOrden]);
            }

            $db->commit();
            return ['success' => true, 'codigo' => $codigo, 'id_orden' => $id_orden];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

// Obtener órdenes activas para la pantalla de entregas del cajero
    public static function getOrdenesParaEntrega() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT o.id_orden, 
                   o.codigo, 
                   o.total, 
                   e.nombre AS estado_nombre, 
                   o.id_estado,
                   COALESCE(u.nombre, 'Cliente Mostrador') AS cliente
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
            WHERE DATE(o.fecha_creacion) = CURDATE() AND o.id_estado IN (1, 2, 3)
            ORDER BY o.id_estado DESC, o.fecha_creacion ASC
        ");
        return $stmt->fetchAll();
    }

    // Cambiar estado a Entregada (id_estado = 4)
    public static function marcarComoEntregada($id_orden) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ordenes SET id_estado = 4 WHERE id_orden = ?");
        return $stmt->execute([$id_orden]);
    }

}


