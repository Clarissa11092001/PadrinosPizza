<?php
// layouts/fix_users.php
require_once '../config/conexion.php';
/** @var PDO $pdo */

$password = '123456';
$hash_nuevo = password_hash($password, PASSWORD_DEFAULT);

// Actualizar los hashes correctamente usando la función nativa de PHP
$stmt = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE username IN ('admin', 'cajero1', 'cocina1')");
$stmt->execute(['hash' => $hash_nuevo]);

echo "<h3>¡Contraseñas actualizadas con éxito!</h3>";
echo "<p>Los usuarios <b>admin</b>, <b>cajero1</b> y <b>cocina1</b> ahora tienen configurada correctamente la contraseña: <b>123456</b></p>";
echo "<p><a href='login.php' style='background: #1B4332; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login</a></p>";
?>