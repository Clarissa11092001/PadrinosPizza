<?php
require_once __DIR__ . '/../database/Conexion.php';

class CocinaModel {
    public static function getOrdenesDelDia() {
        $db = Database::getConnection();
        
        // Se ordena por id_orden DESC para que la orden procesada/movida recientemente quede arriba
        $stmt = $db->query("
            SELECT o.id_orden, o.codigo, o.cliente, o.hora_entrega, o.observaciones AS notas_orden,
                e.nombre AS estado, e.id_estado,
                o.fecha_creacion, 
                TIMESTAMPDIFF(MINUTE, o.fecha_creacion, NOW()) AS minutos,
                IF(DATE(o.fecha_creacion) < CURDATE(), 1, 0) AS es_dia_anterior,
                d.id_detalle, p.nombre AS producto, d.cantidad, d.notas AS notas_item,
                (SELECT GROUP_CONCAT(x.nombre SEPARATOR ', ') 
                    FROM detalle_orden_extra dx 
                    JOIN extras x ON x.id_extra = dx.id_extra 
                    WHERE dx.id_detalle = d.id_detalle) AS extras
            FROM ordenes o
            JOIN estados_orden e ON e.id_estado = o.id_estado
            JOIN detalle_orden d ON d.id_orden = o.id_orden
            JOIN productos p ON p.id_producto = d.id_producto
            WHERE (DATE(o.fecha_creacion) = CURDATE() OR e.nombre IN ('Pendiente', 'En preparación', 'Lista'))
            ORDER BY o.fecha_actualizacion DESC, d.id_detalle ASC
        ");
        
        $filas = $stmt->fetchAll();
        $ordenes = [
            'Pendiente' => [],
            'En preparación' => [],
            'Lista' => [],
            'Entregada' => []
        ];

        foreach ($filas as $fila) {
            $id = $fila['id_orden'];
            $estado = $fila['estado'];
            
            if (!isset($ordenes[$estado][$id])) {
                $ordenes[$estado][$id] = [
                    'id_orden'     => $id,
                    'codigo'       => $fila['codigo'],
                    'cliente'      => $fila['cliente'] ?? 'Cliente General',
                    'hora_entrega' => $fila['hora_entrega'] ? date('h:i A', strtotime($fila['hora_entrega'])) : null,
                    'notas_orden'  => $fila['notas_orden'],
                    'minutos'      => $fila['minutos'],
                    'id_estado'    => $fila['id_estado'],
                    'es_dia_anterior' => intval($fila['es_dia_anterior']),
                    'detalles'     => []
                ];
            }
            $ordenes[$estado][$id]['detalles'][] = [
                'cantidad' => $fila['cantidad'],
                'producto' => $fila['producto'],
                'extras'   => $fila['extras'],
                'notas'    => $fila['notas_item']
            ];
        }
        return $ordenes;
    }

public static function actualizarEstado($id_orden, $nuevo_estado_id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE ordenes SET id_estado = ?, fecha_actualizacion = NOW() WHERE id_orden = ?");
    return $stmt->execute([$nuevo_estado_id, $id_orden]);
    }
}
?> 