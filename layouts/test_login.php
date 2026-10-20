<?php
// layouts/test_login.php
require_once '../config/conexion.php';

/** @var PDO $pdo */

$username = 'admin';
$password_prueba = '123456';

echo "<h3>Prueba de Diagnóstico de Login</h3>";

// 1. Buscar usuario
$stmt = $pdo->prepare("SELECT u.*, r.nombre AS nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red;'>❌ El usuario '{$username}' NO existe en la base de datos.</p>";
} else {
    echo "<p style='color:green;'>✔ Usuario encontrado: <b>{$user['nombre']} {$user['apellido']}</b> (Rol: {$user['nombre_rol']})</p>";
    
    // 2. Probar password_verify
    if (password_verify($password_prueba, $user['password_hash'])) {
        echo "<p style='color:green;'>✔ ¡La contraseña coincide perfectamente con el hash!</p>";
    } else {
        echo "<p style='color:red;'>❌ La contraseña NO coincide con el hash guardado.</p>";
        
        // Generar un nuevo hash correcto para que veas qué pasa
        $nuevo_hash = password_hash($password_prueba, PASSWORD_DEFAULT);
        echo "<p>Hash actual en BD: {$user['password_hash']}</p>";
        echo "<p>Hash que debería tener: {$nuevo_hash}</p>";
    }
}
?>