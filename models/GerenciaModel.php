<?php
require_once __DIR__ . '/../database/conexion.php';

class GerenciaModel {

    // 1. Obtener métricas KPI del día
    public static function getKpisDelDia() {
        $db = Database::getConnection();

        // Ventas Totales del Día
        $stmtVentas = $db->query("
            SELECT COALESCE(SUM(total), 0) AS total_ventas 
            FROM ordenes 
            WHERE DATE(fecha_creacion) = CURDATE() AND id_estado != 5
        ");
        $totalVentas = $stmtVentas->fetch()['total_ventas'];

        // Órdenes Atendidas / Despachadas / Activas
        $stmtOrdenes = $db->query("
            SELECT 
                COUNT(*) AS total_ordenes,
                SUM(CASE WHEN id_estado = 4 THEN 1 ELSE 0 END) AS despachadas,
                SUM(CASE WHEN id_estado IN (1,2,3) THEN 1 ELSE 0 END) AS activas
            FROM ordenes 
            WHERE DATE(fecha_creacion) = CURDATE() AND id_estado != 5
        ");
        $ordenesInfo = $stmtOrdenes->fetch();

        // Anulaciones y Mermas (Estado 5 = Cancelada/Anulada)
        $stmtAnuladas = $db->query("
            SELECT COUNT(*) AS cantidad_anuladas, COALESCE(SUM(total), 0) AS monto_mermas 
            FROM ordenes 
            WHERE DATE(fecha_creacion) = CURDATE() AND id_estado = 5
        ");
        $anuladasInfo = $stmtAnuladas->fetch();

        // Producto Estrella del Día
        $stmtEstrella = $db->query("
            SELECT p.nombre, SUM(d.cantidad) AS total_vendido
            FROM detalle_orden d
            JOIN productos p ON p.id_producto = d.id_producto
            JOIN ordenes o ON o.id_orden = d.id_orden
            WHERE DATE(o.fecha_creacion) = CURDATE() AND o.id_estado != 5
            GROUP BY p.id_producto, p.nombre
            ORDER BY total_vendido DESC
            LIMIT 1
        ");
        $productoEstrella = $stmtEstrella->fetch() ?: ['nombre' => 'N/A', 'total_vendido' => 0];

        return [
            'totalVentas' => $totalVentas,
            'totalOrdenes' => $ordenesInfo['total_ordenes'] ?: 0,
            'despachadas' => $ordenesInfo['despachadas'] ?: 0,
            'activas' => $ordenesInfo['activas'] ?: 0,
            'cantidadAnuladas' => $anuladasInfo['cantidad_anuladas'] ?: 0,
            'montoMermas' => $anuladasInfo['monto_mermas'],
            'productoEstrella' => $productoEstrella['nombre'],
            'unidadesEstrella' => $productoEstrella['total_vendido']
        ];
    }

    // 2. Lista para la Auditoría de Pedidos: las de hoy + las ACTIVAS de días anteriores (atrasadas),
    //    para que gerencia pueda verlas y, si hace falta, anularlas.
    public static function getAuditoriaOrdenes() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT o.id_orden, o.codigo,
                   COALESCE(o.cliente, 'Cliente Mostrador') AS cliente,
                   u.nombre AS cajero,
                   o.total, e.nombre AS estado, o.id_estado,
                   o.motivo_anulacion,
                   o.fecha_creacion,
                   IF(DATE(o.fecha_creacion) < CURDATE(), 1, 0) AS es_atrasada,
                   COALESCE((SELECT SUM(pg.monto) FROM pagos pg WHERE pg.id_orden = o.id_orden), 0) AS pagado,
                   (SELECT m.nombre
                      FROM pagos pg2
                      JOIN metodos_pago m ON m.id_metodo_pago = pg2.id_metodo_pago
                     WHERE pg2.id_orden = o.id_orden
                     ORDER BY pg2.id_pago LIMIT 1) AS metodo_pago
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
            WHERE DATE(o.fecha_creacion) = CURDATE() OR o.id_estado IN (1, 2, 3)
            ORDER BY es_atrasada DESC, o.fecha_creacion DESC
            LIMIT 50
        ");
        return $stmt->fetchAll();
    }

    // 3. Resumen de Ventas por Categoría
    public static function getVentasPorCategoria() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT c.nombre AS categoria, 
                   SUM(d.cantidad) AS cantidad_vendida, 
                   SUM(d.cantidad * d.precio_unitario) AS subtotal
            FROM detalle_orden d
            JOIN productos p ON p.id_producto = d.id_producto
            JOIN categorias c ON c.id_categoria = p.id_categoria
            JOIN ordenes o ON o.id_orden = d.id_orden
            WHERE DATE(o.fecha_creacion) = CURDATE() AND o.id_estado != 5
            GROUP BY c.id_categoria, c.nombre
            ORDER BY subtotal DESC
        ");
        return $stmt->fetchAll();
    }

    // 4. Anular una orden. NUNCA se borra: pasa a estado 5 (Cancelada) y queda registrado
    //    quién la anuló, cuándo y por qué. Reglas:
    //      - Solo se anulan órdenes Pendiente / En preparación / Lista (estados 1, 2, 3).
    //      - El motivo es obligatorio.
    //      - Si ya estaba cobrada, hay que confirmar que el dinero se devolvió al cliente.
    // Devuelve ['success' => bool, 'message' => string]
    public static function anularOrden($id_orden, $motivo, $id_usuario_anula, $reembolsoConfirmado = false) {
        $db = Database::getConnection();

        $motivo = trim((string)$motivo);
        if (mb_strlen($motivo) < 5) {
            return ['success' => false, 'message' => 'Debes escribir el motivo de la anulación (mínimo 5 caracteres).'];
        }

        try {
            $db->beginTransaction();

            // Se bloquea la fila para que dos personas no anulen/entreguen la misma orden a la vez
            $stmt = $db->prepare("SELECT codigo, id_estado FROM ordenes WHERE id_orden = ? FOR UPDATE");
            $stmt->execute([$id_orden]);
            $orden = $stmt->fetch();
            if (!$orden) {
                throw new Exception('La orden no existe.');
            }

            $estado = intval($orden['id_estado']);
            if ($estado === 5) {
                throw new Exception('La orden ' . $orden['codigo'] . ' ya estaba anulada.');
            }
            if ($estado === 4) {
                throw new Exception('La orden ' . $orden['codigo'] . ' ya fue entregada; no se puede anular.');
            }
            if (!in_array($estado, [1, 2, 3], true)) {
                throw new Exception('La orden no se puede anular en su estado actual.');
            }

            // ¿Ya se cobró? Entonces el dinero tiene que haberse devuelto
            $stmtPago = $db->prepare("
                SELECT COALESCE(SUM(pg.monto), 0) AS pagado, MIN(m.nombre) AS metodo
                FROM pagos pg
                JOIN metodos_pago m ON m.id_metodo_pago = pg.id_metodo_pago
                WHERE pg.id_orden = ?
            ");
            $stmtPago->execute([$id_orden]);
            $pago = $stmtPago->fetch();
            $pagado = (float)$pago['pagado'];

            $motivoFinal = $motivo;
            if ($pagado > 0) {
                if (!$reembolsoConfirmado) {
                    throw new Exception('La orden ya fue cobrada ($' . number_format($pagado, 2) . ' en ' . $pago['metodo'] . '). Confirma que el dinero fue devuelto al cliente para poder anularla.');
                }
                $motivoFinal = '[Reembolsado ' . $pago['metodo'] . ' $' . number_format($pagado, 2) . '] ' . $motivo;
            }
            $motivoFinal = mb_substr($motivoFinal, 0, 255);

            // Las observaciones del cliente NO se tocan; el motivo va en su propia columna
            $stmtUp = $db->prepare("
                UPDATE ordenes
                SET id_estado = 5, motivo_anulacion = ?, id_usuario_anula = ?, fecha_anulacion = NOW()
                WHERE id_orden = ? AND id_estado IN (1, 2, 3)
            ");
            $stmtUp->execute([$motivoFinal, $id_usuario_anula, $id_orden]);
            if ($stmtUp->rowCount() === 0) {
                throw new Exception('No se pudo anular la orden porque su estado cambió. Actualiza la página.');
            }

            $db->commit();
            return ['success' => true, 'message' => 'Orden ' . $orden['codigo'] . ' anulada correctamente.'];

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>