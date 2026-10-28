<?php
require_once __DIR__ . '/../database/Conexion.php';

class CierreModel {

    // Obtener la caja abierta de un usuario
    public static function getCajaActiva($id_usuario) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM cierres_caja 
            WHERE id_usuario = ? AND estado = 'abierta' 
            ORDER BY id_cierre DESC LIMIT 1
        ");
        $stmt->execute([$id_usuario]);
        return $stmt->fetch();
    }

    // Abrir un nuevo turno de caja
    public static function abrirCaja($id_usuario, $monto_inicial = 0.00) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO cierres_caja (id_usuario, monto_inicial, estado) 
            VALUES (?, ?, 'abierta')
        ");
        return $stmt->execute([$id_usuario, $monto_inicial]);
    }

    // Calcular las ventas realizadas durante el turno activo
    public static function getTotalVentasTurno($id_usuario, $fecha_apertura) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(total), 0) AS total_ventas, COUNT(*) AS total_ordenes
            FROM ordenes 
            WHERE id_usuario = ? AND fecha_creacion >= ? AND id_estado != 5
        ");
        $stmt->execute([$id_usuario, $fecha_apertura]);
        return $stmt->fetch();
    }

    // Contar órdenes no finalizadas en el turno (Pendientes 1 o En Preparación 2)
    public static function contarOrdenesPendientesTurno($id_usuario, $fecha_apertura) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) AS pendientes
            FROM ordenes 
            WHERE id_usuario = ? AND fecha_creacion >= ? AND id_estado IN (1, 2)
        ");
        $stmt->execute([$id_usuario, $fecha_apertura]);
        $row = $stmt->fetch();
        return $row ? intval($row['pendientes']) : 0;
    }

    // Registrar el cierre de caja y guardar la auditoría
    public static function procesarCierre($id_cierre, $monto_esperado, $monto_declarado, $diferencia, $observaciones) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE cierres_caja 
            SET fecha_cierre = CURRENT_TIMESTAMP,
                monto_esperado = ?,
                monto_declarado = ?,
                diferencia = ?,
                observaciones = ?,
                estado = 'cerrada'
            WHERE id_cierre = ?
        ");
        return $stmt->execute([$monto_esperado, $monto_declarado, $diferencia, $observaciones, $id_cierre]);
    }
}
?>