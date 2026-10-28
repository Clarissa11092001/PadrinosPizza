<?php
// Iniciar sesión únicamente si no existe una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /Padrino's_Pizza/login.php");
    exit();
}
?>