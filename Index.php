<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Padrino's Pizza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-padrino { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="h-screen w-screen relative flex items-center justify-center bg-gray-950 overflow-hidden">

    <!-- Imagen de fondo (Ruta relativa que evita errores con el apóstrofe) -->
    <div class="absolute inset-0 bg-cover bg-center z-0 scale-105 transform transition duration-1000" 
         style="background-image: url('assets/IMG/Inicio_foto.png');">
    </div>

    <!-- Capa oscura sutil sobre la imagen de fondo -->
    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <!-- Tarjeta Central Transparente y más Amplia -->
    <main class="relative z-10 text-center px-10 py-12 w-full max-w-3xl mx-4 bg-black/40 backdrop-blur-md rounded-3xl border border-[#D4AF37]/50 shadow-2xl flex flex-col items-center justify-between my-auto">
        
        <!-- Logo -->
        <img src="assets/IMG/logo_Padrino'sPizza.png" 
             alt="Padrino's Pizza" 
             class="h-28 w-auto mb-6 drop-shadow-md object-contain" 
             onerror="this.style.display='none'">

        <!-- Mensajes de Bienvenida -->
        <div class="space-y-3 mb-8">
            <span class="text-xs md:text-sm font-bold text-[#D4AF37] tracking-[0.2em] uppercase block">
                ¡Bienvenido a la Intranet de Operaciones!
            </span>
            
            <h1 class="text-4xl md:text-6xl font-extrabold text-white font-padrino tracking-wider drop-shadow-lg">
                PADRINO'S PIZZA
            </h1>

            <p class="text-gray-300 text-sm md:text-base max-w-lg mx-auto font-light leading-relaxed pt-2">
                Sistema centralizado para la gestión de órdenes, punto de venta y monitoreo en tiempo real.
            </p>
        </div>

        <!-- Botón para ir al Login -->
        <div class="mb-8">
            <a href="login.php" 
               class="inline-flex items-center space-x-3 bg-[#A61C1C] hover:bg-red-800 text-white font-bold text-base md:text-lg px-10 py-4 rounded-xl shadow-xl transition transform hover:-translate-y-0.5 active:translate-y-0">
                <span>Ingresar al Sistema</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>

        <!-- Footer del Panel -->
        <div class="pt-4 border-t border-white/10 w-full max-w-md">
            <p class="text-xs text-gray-400 font-light tracking-wide">
                © 2026 Padrino's Pizza. Todos los derechos reservados.
            </p>
            <span class="text-[10px] text-gray-500 font-mono block mt-1">V1.1.0</span>
        </div>

    </main>

</body>
</html>