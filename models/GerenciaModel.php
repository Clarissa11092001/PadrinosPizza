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

    // 2. Obtener lista para la Auditoría de Pedidos (Últimas órdenes del día)
    public static function getAuditoriaOrdenes() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT o.id_orden, o.codigo, COALESCE(u.nombre, 'Cliente Mostrador') AS cliente, 
                   o.total, e.nombre AS estado, o.id_estado, o.observaciones
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
            WHERE DATE(o.fecha_creacion) = CURDATE()
            ORDER BY o.fecha_creacion DESC
            LIMIT 10
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

    // 4. Anular Orden (Soft Delete / Cambio de Estado a 5 - Cancelada)
    public static function anularOrden($id_orden, $motivo) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ordenes SET id_estado = 5, observaciones = ? WHERE id_orden = ?");
        return $stmt->execute([$motivo, $id_orden]);
    }
}
?>