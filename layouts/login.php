<?php
session_start();
require_once '../config/conexion.php';

/** @var PDO $pdo */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        // Consultar usuario y su respectivo rol en la base de datos
        $stmt = $pdo->prepare("
            SELECT u.*, r.nombre AS nombre_rol 
            FROM usuarios u 
            JOIN roles r ON u.id_rol = r.id_rol 
            WHERE u.username = :username AND u.activo = 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Validar contraseña encriptada
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre']     = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['rol']        = strtolower($user['nombre_rol']); // administrador, cajero, cocina

            // Redirección automática según el rol asignado
            if ($_SESSION['rol'] === 'administrador') {
                header('Location: admin.php');
            } elseif ($_SESSION['rol'] === 'cajero') {
                header('Location: cajero.php');
            } elseif ($_SESSION['rol'] === 'cocina') {
                header('Location: cocina.php');
            } else {
                header('Location: cajero.php');
            }
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - Iniciar Sesión</title>
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --verde-oscuro: #1B4332;
            --dorado: #D4AF37;
            --rojo-pepperoni: #A61C1C;
            --blanco-crema: #FFF8E7;
            --texto-oscuro: #2D3748;
            --gris-borde: #E2E8F0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--verde-oscuro);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            background-color: #ffffff;
            border: 2px solid var(--dorado);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        }

        .login-header {
            background-color: var(--verde-oscuro);
            padding: 25px 20px 20px 20px;
            text-align: center;
            border-bottom: 2px solid var(--dorado);
        }

        /* Estilo para la imagen del Logo */
        .logo-img {
            max-width: 110px;
            height: auto;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .logo-title {
            font-family: 'Cinzel', serif;
            color: var(--dorado);
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }

        .subtitle {
            color: var(--blanco-crema);
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .login-form {
            padding: 30px 28px;
            background-color: #ffffff;
        }

        .alert-error {
            background-color: #F8D7DA;
            color: var(--rojo-pepperoni);
            border: 1px solid #F5C6CB;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--texto-oscuro);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--texto-oscuro);
            background-color: #ffffff;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 16px;
            margin-top: 10px;
            background-color: var(--rojo-pepperoni);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }

        .btn-submit:hover {
            background-color: #881616;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Encabezado corporativo con Logo -->
        <div class="login-header">
            <!-- Recuerda verificar la ruta de la imagen según tu carpeta -->
            <img src="../assets/Img/image.png" alt="Padrino's Pizza Logo" class="logo-img" onerror="this.style.display='none'">
            
            <h1 class="logo-title">PADRINO'S PIZZA</h1>
            <p class="subtitle">SISTEMA DE CONTROL DE ÓRDENES • EST. 2026</p>
        </div>

        <!-- Formulario PHP -->
        <form class="login-form" action="login.php" method="POST">
            
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="usuario">Usuario / Credencial</label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    placeholder="Ej. admin, cajero1, cocina1" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                >
            </div>

            <button type="submit" class="btn-submit">Ingresar al Sistema</button>
        </form>
    </div>

</body>
</html>