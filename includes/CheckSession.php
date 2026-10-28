<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cierre de sesión completo y forzado si viene el parámetro ?logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    header("Location: login.php");
    exit();
}

// 2. Redirección si el usuario YA tiene una sesión activa
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['id_rol'] == 3) {
        header("Location: controllers/CocinaController.php");
    } else if ($_SESSION['id_rol'] == 1) {
        header("Location: controllers/GerenciaController.php");
    } else {
        header("Location: controllers/PosController.php");
    }
    exit();
}
?>