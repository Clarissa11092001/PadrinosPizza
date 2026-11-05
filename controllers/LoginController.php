<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/UsuarioModel.php';

// ACCIÓN 1: Cierre de Sesión (Logout)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: ../login.php?msg=sesion_cerrada");
    exit();
}

// ACCIÓN 2: Procesar Inicio de Sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header("Location: ../login.php?error=Completa+todos+los+campos");
        exit();
    }

    // Consultar usuario en la base de datos
    $usuario = UsuarioModel::obtenerPorUsername($username);

    // 1. Validar si el usuario existe y la contraseña encriptada coincide
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        
        // 2. Guardar variables de sesión leyendo el rol registrado en MySQL
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['id_rol']     = $usuario['id_rol'];
        $_SESSION['rol_nombre'] = $usuario['rol'] ?? 'Usuario';

        // 3. Redireccionar automáticamente según el rol guardado en la BD
        if ($usuario['id_rol'] == 3) { 
            // Rol 3: Cocina
            header("Location: CocinaController.php");
        } else if ($usuario['id_rol'] == 1) { 
            // Rol 1: Gerencia / Admin
            header("Location: GerenciaController.php");
        } else { 
            // Rol 2: Cajero -> Va al POS
            header("Location: PosController.php");
        }
        exit();

    } else {
        header("Location: ../login.php?error=Credenciales+incorrectas");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>