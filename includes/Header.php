<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$idRol = $_SESSION['id_rol'] ?? 2; // 1: Admin/Gerencia, 2: Cajero, 3: Cocina
$vistaActual = $vistaActual ?? ''; // Identificador de la vista activa (ej. 'pos', 'entregas', 'cocina', 'gerencia')

// URL de un asset con ?v=<fecha de modificación>: cada vez que cambias el archivo el navegador
// lo descarga de nuevo (adiós a "no se actualiza el JS aunque lo cambié").
if (!function_exists('asset_v')) {
    function asset_v(string $rutaRelativa): string {
        $archivo = __DIR__ . '/../' . $rutaRelativa;
        $version = is_file($archivo) ? filemtime($archivo) : time();
        return "/Padrino's_Pizza/" . $rutaRelativa . '?v=' . $version;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Padrino's Pizza</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-padrino { font-family: 'Cinzel', serif; }
  </style>
  <?php if (isset($extraCss)): ?>
    <link rel="stylesheet" href="<?= $extraCss ?>">
  <?php endif; ?>
</head>
<body class="<?= $bodyClasses ?? 'bg-[#0E1A14] text-white min-h-screen' ?>">

  <header class="bg-[#1B4332] border-b-2 border-[#D4AF37] px-6 py-3 flex justify-between items-center shadow-lg text-white sticky top-0 z-50">
    <!-- Logotipo y Nombre -->
    <div class="flex items-center space-x-4">
      <h1 class="text-xl font-bold text-[#D4AF37] font-padrino tracking-wider">PADRINO'S PIZZA</h1>
      

    </div>

    <!-- Botones Dinámicos de Navegación por Rol y Vista -->
    <div class="flex items-center space-x-3">
      
      <?php if ($idRol == 2): // ROL CAJERO ?>
        
        <?php if ($vistaActual === 'entregas'): ?>
          <!-- En la pantalla de Entregas -->
          <a href="/Padrino's_Pizza/controllers/PosController.php" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition shadow">
            ← Volver al POS
          </a>
        <?php else: ?>
          <!-- En la pantalla del POS -->
          <a href="/Padrino's_Pizza/controllers/EntregaController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
             Entregas
          </a>
          <button onclick="abrirModalCierre()" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition shadow cursor-pointer">
             Cierre de Caja y Salir
          </button>
        <?php endif; ?>

      <?php elseif ($idRol == 1): // ROL ADMINISTRADOR / GERENCIA ?>
        
        <a href="/Padrino's_Pizza/controllers/PosController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
          Ir al POS
        </a>
        <a href="/Padrino's_Pizza/controllers/GerenciaController.php" class="bg-[#D4AF37] hover:bg-amber-500 text-gray-900 font-bold px-3 py-1.5 rounded-lg text-xs transition">
           Reportes
        </a>
        <a href="/Padrino's_Pizza/controllers/LoginController.php?action=logout" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition shadow">
          Cerrar Sesión
        </a>

      <?php else: // ROL COCINA ?>
        
        <a href="/Padrino's_Pizza/controllers/LoginController.php?action=logout" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition shadow">
          Salir de Cocina
        </a>

      <?php endif; ?>

    </div>
  </header>