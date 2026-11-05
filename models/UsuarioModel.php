<?php
require_once __DIR__ . '/../database/conexion.php';

class UsuarioModel {
    public static function obtenerPorUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id_usuario, u.id_rol, u.nombre, u.username, u.password_hash, r.nombre AS rol 
            FROM usuarios u 
            JOIN roles r ON r.id_rol = u.id_rol 
            WHERE u.username = ? AND u.activo = 1
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}
?>