<?php
// config/conexion.php

$host = '127.0.0.1';
$port = '3306'; // Si tu WAMP usa otro puerto, cámbialo aquí (ej. 3307)
$db   = 'padrinos_pizza';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Esto te dirá exactamente si falló la conexión y por qué
    die("Error de conexión a la base de datos: " . $e->getMessage());
}