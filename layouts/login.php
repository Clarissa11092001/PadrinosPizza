<?php
// Iniciar sesión para gestionar autenticación
session_start();

$error = '';

// Procesar el formulario cuando se envía mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol      = trim($_POST['rol'] ?? '');

    // Ejemplo básico de validación (aquí conectas con tu base de datos)
    if (!empty($usuario) && !empty($password) && !empty($rol)) {
        // Lógica de autenticación previa
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol']     = $rol;
        
        // Redireccionar al panel principal según el rol
        // header('Location: dashboard.php');
        // exit;
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - Login</title>
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- CSS integrado directamente -->
    <style>
        :root {
            --verde-oscuro: #1B4332;
            --dorado: #D4AF37;
            --dorado-oro: #C5A059;
            --marron-madera: #8B5A2B;
            --rojo-pepperoni: #A61C1C;
            --amarillo-queso: #F4C430;
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
            max-width: 110px; /* Ajusta este tamaño según las dimensiones de tu imagen */
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

        .form-group input,
        .form-group select {
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

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232D3748' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 14px;
            padding-right: 36px;
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
            <!-- Cambia "../assets/Img/logo.png" por el nombre exacto de tu archivo de imagen -->
            <img src="../assets/Img/image.png" alt="Padrino's Pizza Logo" class="logo-img">
            
            <h1 class="logo-title">PADRINO'S PIZZA</h1>
            <p class="subtitle">SISTEMA DE CONTROL DE ÓRDENES • EST. 2026</p>
        </div>

        <!-- Formulario PHP -->
        <form class="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="usuario">Usuario / Código</label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : 'admin'; ?>" 
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

            <div class="form-group">
                <label for="rol">Rol de Acceso</label>
                <select id="rol" name="rol" required>
                    <option value="cajero" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'cajero') ? 'selected' : ''; ?>>Cajero / Mostrador</option>
                    <option value="administrador" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="cocina" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'cocina') ? 'selected' : ''; ?>>Cocina / Preparación</option>
                    <option value="repartidor" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'repartidor') ? 'selected' : ''; ?>>Repartidor</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Ingresar al Sistema</button>
        </form>
    </div>

</body>
</html>