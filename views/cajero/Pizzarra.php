<?php
/**
 * @var array $ordenes
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Padrino's Pizza - Control de Entregas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-padrino { font-family: 'Cinzel', serif; }
  </style>
</head>
<body class="bg-[#0E1A14] text-white min-h-screen">

  <header class="bg-[#1B4332] border-b-2 border-[#D4AF37] px-6 py-3 flex justify-between items-center shadow-md">
    <div class="flex items-center space-x-4">
      <h1 class="text-xl font-bold text-[#D4AF37] font-padrino tracking-wider">PADRINO'S PIZZA</h1>
      <span class="bg-[#D4AF37]/15 text-[#D4AF37] border border-[#D4AF37] text-xs px-3 py-1 rounded-full font-semibold">
        Caja - Control y Entrega de Pedidos
      </span>
    </div>
    <a href="PosController.php" class="bg-[#A61C1C] hover:bg-red-800 text-white font-bold px-4 py-1.5 rounded-lg text-xs transition">
      ← Volver al POS
    </a>
  </header>

  <div class="container mx-auto p-6 max-w-6xl">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-[#D4AF37] font-padrino">Progreso de Pedidos en Vivo</h2>
      <button onclick="location.reload()" class="bg-[#1B4332] border border-[#D4AF37] text-white text-xs px-3 py-1.5 rounded hover:bg-[#D4AF37]/20 transition flex items-center space-x-1">
        <span>🔄 Actualizar</span>
      </button>
    </div>

    <!-- Grid de Tarjetas de Órdenes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if (empty($ordenes)): ?>
        <div class="col-span-full bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-8 text-center text-gray-400">
          No hay pedidos activos por entregar en este momento.
        </div>
      <?php endif; foreach ($ordenes as $ord): ?>
        <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg flex flex-col justify-between">
          <div>
            <div class="flex justify-between items-start mb-3">
              <div>
                <span class="text-xs font-bold text-gray-400 block">ORDEN</span>
                <span class="text-xl font-bold text-[#D4AF37]">#<?= htmlspecialchars($ord['codigo']) ?></span>
              </div>
              
              <!-- Badge de Estado -->
              <?php if ($ord['id_estado'] == 3): ?>
                <span class="bg-emerald-900/60 border border-emerald-500 text-emerald-400 text-xs font-bold px-2.5 py-1 rounded shadow">
                  🟢 LISTO EN BARRA
                </span>
              <?php elseif ($ord['id_estado'] == 2): ?>
                <span class="bg-amber-900/60 border border-amber-500 text-amber-400 text-xs font-bold px-2.5 py-1 rounded">
                  🟡 EN HORNO / COCINA
                </span>
              <?php else: ?>
                <span class="bg-red-900/60 border border-red-500 text-red-400 text-xs font-bold px-2.5 py-1 rounded">
                  🔴 EN ESPERA (COCINA)
                </span>
              <?php endif; ?>
            </div>

            <div class="text-sm text-gray-300 mb-4 space-y-1.5 border-t border-b border-white/5 py-3">
              <p><strong class="text-gray-400">Atendido por / Cliente:</strong> <?= htmlspecialchars($ord['cliente']) ?></p>
              <p><strong class="text-gray-400">Monto Total:</strong> <span class="text-[#D4AF37] font-bold text-base">$<?= number_format($ord['total'], 2) ?></span></p>
            </div>
          </div>

          <!-- Botón de Acción Exclusivo para el Cajero -->
          <div class="mt-2">
            <?php if ($ord['id_estado'] == 3): ?>
              <form method="POST" action="EntregaController.php">
                <input type="hidden" name="id_orden" value="<?= $ord['id_orden'] ?>">
                <button type="submit" class="w-full bg-[#1B4332] hover:bg-emerald-800 border border-[#D4AF37] text-white font-bold py-2.5 rounded-lg text-sm transition shadow">
                  ✔ Marcar como Entregada
                </button>
              </form>
            <?php else: ?>
              <button disabled class="w-full bg-gray-800/80 border border-gray-700 text-gray-500 font-semibold py-2 rounded-lg text-xs cursor-not-allowed">
                Esperando a Cocina...
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>