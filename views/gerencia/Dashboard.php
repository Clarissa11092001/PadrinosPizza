<?php

/**
 * Variables inyectadas por GerenciaController.php:
 * @var array $kpis
 * @var array $auditoria
 * @var array $ventasCategoria
 * @var array|null $flash
 */
$bodyClasses = 'bg-[#0E1A14] text-white min-h-screen font-sans';
$vistaActual = 'gerencia';
require_once __DIR__ . '/../../includes/header.php';
?>

  <div class="container mx-auto p-6 max-w-7xl">

    <?php if (!empty($flash)): ?>
      <div class="mb-6 rounded-xl px-4 py-3 text-sm font-semibold border <?= $flash['tipo'] === 'ok' ? 'bg-emerald-900/40 text-emerald-300 border-emerald-500' : 'bg-red-900/40 text-red-300 border-red-500' ?>">
        <?= $flash['tipo'] === 'ok' ? '✔' : '⚠️' ?> <?= htmlspecialchars($flash['mensaje']) ?>
      </div>
    <?php endif; ?>

    <!-- Tarjetas de Métricas Principales (KPIs Dinámicos) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      
      <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ventas Totales (Día)</h3>
        <div class="text-3xl font-extrabold text-[#D4AF37] my-2">$<?= number_format($kpis['totalVentas'], 2) ?></div>
        <div class="text-xs text-emerald-400 font-medium">Ventas confirmadas hoy</div>
      </div>

      <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Órdenes Atendidas</h3>
        <div class="text-3xl font-extrabold text-[#D4AF37] my-2"><?= $kpis['totalOrdenes'] ?></div>
        <div class="text-xs text-emerald-400 font-medium"><?= $kpis['despachadas'] ?> Despachadas | <?= $kpis['activas'] ?> Activas</div>
      </div>

      <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Anulaciones y Mermas</h3>
        <div class="text-3xl font-extrabold text-red-500 my-2">$<?= number_format($kpis['montoMermas'], 2) ?></div>
        <div class="text-xs text-red-400 font-medium"><?= $kpis['cantidadAnuladas'] ?> Órdenes Anuladas</div>
      </div>

      <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Producto Estrella</h3>
        <div class="text-2xl font-extrabold text-[#D4AF37] my-2 truncate"><?= htmlspecialchars($kpis['productoEstrella']) ?></div>
        <div class="text-xs text-emerald-400 font-medium"><?= $kpis['unidadesEstrella'] ?> Unidades Vendidas</div>
      </div>

    </div>

    <!-- Contenido Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Columna Izquierda: Auditoría y Ventas por Categoría -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Tabla de Auditoría -->
        <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
          <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-lg text-[#D4AF37]" style="font-family: 'Cinzel', serif;">Auditoría de Pedidos y Excepciones</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="bg-[#1B4332]/60 text-[#D4AF37] text-xs uppercase border-b border-[#D4AF37]/30">
                  <th class="p-3">Código</th>
                  <th class="p-3">Cliente</th>
                  <th class="p-3">Monto</th>
                  <th class="p-3">Estado</th>
                  <th class="p-3 text-center">Acción (Gerencia)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <?php if (empty($auditoria)): ?>
                  <tr><td colspan="5" class="p-4 text-center text-gray-400">No hay órdenes registradas hoy.</td></tr>
                <?php endif; foreach ($auditoria as $ord): ?>
                <tr class="hover:bg-[#D4AF37]/5 transition">
                  <td class="p-3 font-bold text-white">#<?= htmlspecialchars($ord['codigo']) ?></td>
                  <td class="p-3 text-gray-300">
                    <?= htmlspecialchars($ord['cliente']) ?>
                    <?php if (!empty($ord['es_atrasada'])): ?>
                      <span class="ml-1 bg-red-600 text-white font-extrabold text-[9px] px-1.5 py-0.5 rounded uppercase">Atrasada · <?= date('d/m', strtotime($ord['fecha_creacion'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ord['cajero'])): ?>
                      <span class="block text-[10px] text-gray-500">Caja: <?= htmlspecialchars($ord['cajero']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 font-semibold text-[#D4AF37]">$<?= number_format($ord['total'], 2) ?></td>
                  <td class="p-3">
                    <?php if ($ord['id_estado'] == 5): ?>
                      <span class="px-2 py-1 rounded text-xs font-bold bg-red-900/40 text-red-400 border border-red-500">Anulada</span>
                    <?php else: ?>
                      <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-900/40 text-emerald-400 border border-emerald-500"><?= htmlspecialchars($ord['estado']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 text-center">
                    <?php if (in_array((int)$ord['id_estado'], [1, 2, 3], true)): ?>
                      <button type="button"
                              class="btn-anular bg-[#A61C1C] hover:bg-red-800 text-white text-xs font-bold px-3 py-1.5 rounded transition shadow cursor-pointer"
                              data-id="<?= $ord['id_orden'] ?>"
                              data-codigo="<?= htmlspecialchars($ord['codigo']) ?>"
                              data-cliente="<?= htmlspecialchars($ord['cliente']) ?>"
                              data-total="<?= number_format((float)$ord['total'], 2, '.', '') ?>"
                              data-pagado="<?= number_format((float)$ord['pagado'], 2, '.', '') ?>"
                              data-metodo="<?= htmlspecialchars($ord['metodo_pago'] ?? '') ?>">
                        Anular
                      </button>
                    <?php elseif ((int)$ord['id_estado'] === 5): ?>
                      <span class="text-xs text-gray-400 italic"><?= htmlspecialchars($ord['motivo_anulacion'] ?: 'Anulada') ?></span>
                    <?php else: ?>
                      <span class="text-xs text-gray-500 italic">Entregada</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Ventas por Categoría -->
        <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
          <h2 class="font-bold text-lg text-[#D4AF37] mb-4" style="font-family: 'Cinzel', serif;">Resumen de Ventas por Categoría</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="bg-[#1B4332]/60 text-[#D4AF37] text-xs uppercase border-b border-[#D4AF37]/30">
                  <th class="p-3">Categoría</th>
                  <th class="p-3">Cantidad Vendida</th>
                  <th class="p-3">Ingreso Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <?php foreach ($ventasCategoria as $cat): ?>
                <tr class="hover:bg-[#D4AF37]/5 transition">
                  <td class="p-3 text-white font-medium"><?= htmlspecialchars($cat['categoria']) ?></td>
                  <td class="p-3 text-gray-300"><?= $cat['cantidad_vendida'] ?> uds</td>
                  <td class="p-3 font-semibold text-[#D4AF37]">$<?= number_format($cat['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Columna Derecha: Accesos Directos a Monitoreo y Catálogo -->
      <div class="space-y-6">
        
        <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
          <h2 class="font-bold text-lg text-[#D4AF37] mb-4" style="font-family: 'Cinzel', serif;">Supervisión en Tiempo Real</h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center p-3 bg-[#1B4332]/30 border border-[#D4AF37]/30 rounded-lg">
              <div>
                <strong class="block text-sm text-white">Módulo de Cajero</strong>
                <span class="text-xs text-gray-400">Punto de Venta POS</span>
              </div>
              <a href="../controllers/PosController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded transition">
                Ir a POS
              </a>
            </div>

            <div class="flex justify-between items-center p-3 bg-[#1B4332]/30 border border-[#D4AF37]/30 rounded-lg">
              <div>
                <strong class="block text-sm text-white">Monitor de Cocina</strong>
                <span class="text-xs text-gray-400">Comandero KDS</span>
              </div>
              <a href="../controllers/CocinaController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded transition">
                Ir a Cocina
              </a>
            </div>
          </div>
        </div>

        <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-5 shadow-lg">
          <h2 class="font-bold text-lg text-[#D4AF37] mb-4" style="font-family: 'Cinzel', serif;">Gestión de Catálogo</h2>
          <div class="p-3 bg-[#1B4332]/30 border border-[#D4AF37]/30 rounded-lg flex justify-between items-center">
            <div>
              <strong class="block text-sm text-white">Administrar Menú</strong>
              <span class="text-xs text-gray-400">Editar productos y precios</span>
            </div>
            <a href="../controllers/CatalogoController.php" class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white text-xs font-bold px-3 py-1.5 rounded transition">
              Catálogo
            </a>
          </div>
        </div>

      </div>

    </div>

  </div>

<!-- Componente Modal de Anulación -->
<?php require_once __DIR__ . '/../Components/Modal_Anulacion.php'; ?>

<?php
$extraJs = asset_v('assets/JS/Anulacion.js');
require_once __DIR__ . '/../../includes/footer.php';
?>