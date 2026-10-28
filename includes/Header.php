<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$idRol = $_SESSION['id_rol'] ?? 2;
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

  <header class="bg-[#1B4332] border-b-2 border-[#D4AF37] px-6 py-3 flex justify-between items-center shadow-lg text-white">
    <div class="flex items-center space-x-4">
      <h1 class="text-xl font-bold text-[#D4AF37] font-padrino tracking-wider">PADRINO'S PIZZA</h1>
    </div>

    <div class="flex items-center space-x-3">
      <?php if ($idRol == 2): ?>
        <a href="EntregaController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition"> Entregas </a>
        <button onclick="abrirModalCierre()" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition shadow"> Cierre de Caja y Salir </button>
      <?php elseif ($idRol == 1): ?>
        <a href="LoginController.php?action=logout" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition">Cerrar Sesión</a>
      <?php else: ?>
        <a href="LoginController.php?action=logout" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition"> Salir de Cocina</a>
      <?php endif; ?>
    </div>
  </header>