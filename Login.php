<?php require_once __DIR__ . '/includes/CheckSession.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrino's Pizza - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1 { font-family: 'Cinzel', serif; }
        .bg-padrino-green { background-color: #1B4332; }
        .text-padrino-gold { color: #D4AF37; }
        .bg-padrino-red { background-color: #A61C1C; }
    </style>
</head>
<body class="bg-[#1B4332] min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-[#D4AF37]">
        
        <!-- Header del Formulario -->
        <div class="bg-[#1B4332] p-6 text-center border-b-2 border-[#D4AF37]">
            <h1 class="text-2xl font-bold text-[#D4AF37] tracking-wider mb-1">PADRINO'S PIZZA</h1>
            <p class="text-xs text-amber-200 font-semibold tracking-widest uppercase">SISTEMA DE CONTROL DE ÓRDENES • EST. 2026</p>
        </div>

        <!-- Cuerpo del Formulario -->
        <div class="p-6">
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2.5 rounded-xl mb-4 text-xs font-semibold text-center">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

<form action="controllers/LoginController.php" method="POST" class="space-y-5 py-2">
    
    <!-- Campo 1: Usuario -->
    <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Usuario</label>
        <input type="text" name="username" required placeholder="ej. cajero, admin, cocina" 
               class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B4332] focus:border-[#1B4332] focus:outline-none transition">
    </div>

    <!-- Campo 2: Contraseña -->
    <div>
        <label class="block text-xs font-bold text-gray-700 mb-1.5">Contraseña</label>
        <input type="password" name="password" required placeholder="••••••••" 
               class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-[#1B4332] focus:border-[#1B4332] focus:outline-none transition">
    </div>
        <br>
    <!-- Botón de Envío (Rojo Vino Corporativo con Hover) -->
    <button type="submit" 
            class="w-full bg-[#A61C1C] hover:bg-red-800 text-white font-bold py-3.5 rounded-xl shadow-md transition text-sm mt-2 cursor-pointer active:scale-[0.98]">
        Ingresar al Sistema
    </button>

</form>
        </div>

    </div>

</body>
</html>