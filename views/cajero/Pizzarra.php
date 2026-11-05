<?php
$vistaActual = 'entregas';
require_once __DIR__ . '/../../includes/header.php';
/**
 * @var array $ordenes
 */

// Separar las órdenes en activas (1, 2, 3) e historial entregadas (4)
$ordenesActivas = array_filter($ordenes ?? [], fn($o) => intval($o['id_estado']) < 4);
$ordenesEntregadas = array_filter($ordenes ?? [], fn($o) => intval($o['id_estado']) == 4);
$miCaja = intval($_SESSION['id_usuario'] ?? 0);
$totalAtrasadas = count(array_filter($ordenesActivas, fn($o) => !empty($o['es_atrasada']) && intval($o['id_usuario']) === $miCaja));
?>


  <div class="container mx-auto p-6 max-w-7xl flex-1 space-y-10">

    <!-- ========================================================= -->
    <!-- SECCIÓN 1: PEDIDOS EN PROCESO / POR ENTREGAR (4 COLUMNAS) -->
    <!-- ========================================================= -->
    <section>
    <div class="relative py-4 flex items-center justify-center">
      <div class="flex-grow border-t border-[#D4AF37]/100"></div>
      <span class="flex-shrink mx-4 text-xs font-bold text-[#D4AF37] tracking-widest uppercase bg-[#14281E] px-4 py-1.5 rounded-full border border-[#D4AF37]/40 shadow-sm">
        Progreso de Entregas y Pedidos Activos
      </span>
      <div class="flex-grow border-t border-[#D4AF37]/80"></div>
    </div>

      <?php if ($totalAtrasadas > 0): ?>
        <div class="mb-5 bg-red-950/60 border border-red-500 text-red-200 text-xs font-semibold rounded-xl px-4 py-3 text-center">
          ⚠️ Tienes <?= $totalAtrasadas ?> orden(es) de esta caja de días anteriores sin terminar. Entrégalas (o pide a gerencia que las anule) para poder cerrar caja.
        </div>
      <?php endif; ?>

      <!-- Grid de 4 Columnas para Pedidos Activos -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php if (empty($ordenesActivas)): ?>
          <div class="col-span-full bg-[#14281E] border border-[#D4AF37]/30 rounded-2xl p-8 text-center text-gray-400">
            ✓ No hay pedidos pendientes de entrega en este momento.
          </div>
        <?php else: foreach ($ordenesActivas as $ord): 
            $estaPagado = isset($ord['esta_pagado']) ? intval($ord['esta_pagado']) : 0;
            $detallesJson = htmlspecialchars(json_encode($ord['detalles'] ?? []), ENT_QUOTES, 'UTF-8');
            $esAtrasada = !empty($ord['es_atrasada']);
        ?>
          <div class="bg-[#14281E] border <?= $esAtrasada ? 'border-red-500/80' : 'border-[#D4AF37]/30' ?> rounded-2xl p-4 shadow-lg flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-start mb-2">
                <div>
                  <span class="text-[10px] font-bold text-gray-400 block">ORDEN</span>
                  <span class="text-lg font-bold text-[#D4AF37]">#<?= htmlspecialchars($ord['codigo']) ?></span>
                </div>
                
                <!-- Badge de Estado -->
                <?php if ($ord['id_estado'] == 3): ?>
                  <span class="bg-emerald-900/60 border border-emerald-500 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded shadow animate-pulse">
                    🟢 LISTO EN BARRA
                  </span>
                <?php elseif ($ord['id_estado'] == 2): ?>
                  <span class="bg-amber-900/60 border border-amber-500 text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded">
                    🟡 EN PROCESO
                  </span>
                <?php else: ?>
                  <span class="bg-red-900/60 border border-red-500 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded">
                    🔴 EN ESPERA
                  </span>
                <?php endif; ?>
              </div>

              <?php if ($esAtrasada): ?>
                <span class="inline-block mb-2 bg-red-600 text-white font-extrabold text-[9px] px-2 py-0.5 rounded-md tracking-wider uppercase border border-red-300">
                  ⚠️ Atrasada · del <?= date('d/m', strtotime($ord['fecha_creacion'])) ?>
                </span>
              <?php endif; ?>

              <!-- Detalles de Cliente, Pago y Total -->
              <div class="text-xs text-gray-300 mb-3 space-y-1 border-t border-b border-white/5 py-2">
                <p><strong class="text-gray-400">Cliente:</strong> <?= htmlspecialchars($ord['cliente']) ?></p>
                <?php if (!empty($ord['hora_entrega'])): ?>
                  <p><strong class="text-gray-400">Hora solicitada:</strong> <?= date('h:i A', strtotime($ord['hora_entrega'])) ?></p>
                <?php endif; ?>
                <p><strong class="text-gray-400">Estado Pago:</strong> 
                  <?php if ($estaPagado === 1): ?>
                    <span class="text-emerald-400 font-bold">✓ Pagado</span>
                  <?php else: ?>
                    <span class="text-amber-400 font-bold">⚠️ Por Cancelar</span>
                  <?php endif; ?>
                </p>
                <p><strong class="text-gray-400">Total:</strong> <span class="text-[#D4AF37] font-bold text-sm">$<?= number_format($ord['total'], 2) ?></span></p>
              </div>
            </div>

            <!-- Botón de Acción -->
            <div class="mt-2">
              <?php if ($ord['id_estado'] == 3): ?>
                <button type="button" 
                        class="btn-marcar-entregada w-full bg-[#1B4332] hover:bg-emerald-800 border border-[#D4AF37] text-white font-bold py-2 rounded-xl text-xs transition shadow cursor-pointer"
                        data-id="<?= $ord['id_orden'] ?>"
                        data-codigo="<?= htmlspecialchars($ord['codigo']) ?>"
                        data-cliente="<?= htmlspecialchars($ord['cliente']) ?>"
                        data-total="<?= $ord['total'] ?>"
                        data-pagado="<?= $estaPagado ?>"
                        data-productos='<?= $detallesJson ?>'>
                  ✔ Entregar
                </button>
              <?php else: ?>
                <button disabled class="w-full bg-gray-800/80 border border-gray-700 text-gray-500 font-semibold py-2 rounded-xl text-xs cursor-not-allowed">
                  Esperando a Cocina...
                </button>
              <?php endif; ?>
              <button type="button" class="btn-ver-ticket mt-2 w-full bg-transparent hover:bg-white/5 border border-white/20 text-gray-300 font-semibold py-1.5 rounded-xl text-[11px] transition cursor-pointer"
                      data-id="<?= $ord['id_orden'] ?>">
                 Ver ticket
              </button>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </section>

    <!-- LÍNEA SEPARADORA DE SECCIONES CON ACENTOS DORADOS -->
    <div class="relative py-4 flex items-center justify-center">
      <div class="flex-grow border-t border-[#D4AF37]/100"></div>
      <span class="flex-shrink mx-4 text-xs font-bold text-[#D4AF37] tracking-widest uppercase bg-[#14281E] px-4 py-1.5 rounded-full border border-[#D4AF37]/40 shadow-sm">
        📜 Historial de Despachos del Día
      </span>
      <div class="flex-grow border-t border-[#D4AF37]/80"></div>
    </div>

    <!-- ========================================================= -->
    <!-- SECCIÓN 2: HISTORIAL DE ÓRDENES YA ENTREGADAS            -->
    <!-- ========================================================= -->
    <section class="pb-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php if (empty($ordenesEntregadas)): ?>
          <div class="col-span-full text-center py-6 text-gray-500 text-xs italic">
            Aún no se han completado entregas hoy.
          </div>
        <?php else: foreach ($ordenesEntregadas as $ord): ?>
          <div class="bg-[#102018]/80 border border-emerald-900/40 rounded-2xl p-4 opacity-80 hover:opacity-100 transition shadow-sm">
            <div class="flex justify-between items-start mb-2">
              <div>
                <span class="text-[10px] font-bold text-gray-500 block">ORDEN</span>
                <span class="text-base font-bold text-gray-300">#<?= htmlspecialchars($ord['codigo']) ?></span>
              </div>
              <span class="bg-emerald-950 text-emerald-400 border border-emerald-700/50 text-[10px] font-bold px-2 py-0.5 rounded-md">
                ✓ ENTREGADA
              </span>
            </div>

            <div class="text-xs text-gray-400 space-y-1 border-t border-white/5 pt-2 mt-2">
              <p><strong class="text-gray-300">Cliente:</strong> <?= htmlspecialchars($ord['cliente']) ?></p>
              <p><strong class="text-gray-300">Monto Cobrado:</strong> <span class="text-emerald-400 font-bold">$<?= number_format($ord['total'], 2) ?></span></p>
              <?php if (!empty($ord['fecha_actualizacion'])): ?>
                <p class="text-[10px] text-gray-500 pt-1">Entregada a las <?= date('h:i A', strtotime($ord['fecha_actualizacion'])) ?></p>
              <?php endif; ?>
            </div>

            <button type="button" class="btn-ver-ticket mt-3 w-full bg-transparent hover:bg-white/5 border border-white/20 text-gray-300 font-semibold py-1.5 rounded-xl text-[11px] transition cursor-pointer"
                    data-id="<?= $ord['id_orden'] ?>">
               Ver ticket
            </button>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </section>

  </div>

  <!-- Componente Modal de Cobro -->
  <?php 
  $pathModal = __DIR__ . '/../Components/Modal_CobroEntrega.php';
  if (file_exists($pathModal)) {
      require_once $pathModal;
  }
  ?>

  <!-- Componente Modal del Ticket -->
  <?php require_once __DIR__ . '/../Components/Modal_Ticket.php'; ?>

  <!-- Scripts (asset_v añade ?v=fecha para evitar copias viejas en caché) -->
  <script src="<?= asset_v('assets/JS/Ticket.js') ?>"></script>
  <script src="<?= asset_v('assets/JS/Entrega.js') ?>"></script>
  <script src="<?= asset_v('assets/JS/RefrescarPizarras.js') ?>"></script>

</body>
</html>