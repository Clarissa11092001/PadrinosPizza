<?php
/**
 * Variables inyectadas por GerenciaController.php:
 * @var array $kpis
 * @var array $auditoria
 * @var array $ventasCategoria
 */
$bodyClasses = 'bg-[#0E1A14] text-white min-h-screen font-sans';
require_once __DIR__ . '/../../includes/header.php';
?>

  <div class="container mx-auto p-6 max-w-7xl">

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
                  <th class="p-3">Cajero / Cliente</th>
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
                  <td class="p-3 text-gray-300"><?= htmlspecialchars($ord['cliente']) ?></td>
                  <td class="p-3 font-semibold text-[#D4AF37]">$<?= number_format($ord['total'], 2) ?></td>
                  <td class="p-3">
                    <?php if ($ord['id_estado'] == 5): ?>
                      <span class="px-2 py-1 rounded text-xs font-bold bg-red-900/40 text-red-400 border border-red-500">Anulada</span>
                    <?php else: ?>
                      <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-900/40 text-emerald-400 border border-emerald-500"><?= htmlspecialchars($ord['estado']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 text-center">
                    <?php if ($ord['id_estado'] != 5): ?>
                      <form method="POST" onsubmit="return confirm('¿Seguro que deseas anular la orden #<?= $ord['codigo'] ?>?');" class="inline">
                        <input type="hidden" name="anular_id" value="<?= $ord['id_orden'] ?>">
                        <button type="submit" class="bg-[#A61C1C] hover:bg-red-800 text-white text-xs font-bold px-3 py-1.5 rounded transition shadow">
                          Anular Order
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-xs text-gray-500 italic"><?= htmlspecialchars($ord['observaciones'] ?: 'Anulada') ?></span>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>