<?php
/**
 * Variables inyectadas por el controlador:
 * @var array $ordenes
 * @var int $totalActivas
 */
$bodyClasses = 'bg-gray-900 text-white h-screen flex flex-col font-sans overflow-hidden';
$extraCss = '/Padrino\'s_Pizza/assets/css/Pizarra.css';
require_once __DIR__ . '/../../includes/header.php';
?>

    <!-- Sub-Header específico de cocina -->
    <div class="bg-gray-950 p-2 px-5 border-b border-gray-800 flex justify-between items-center shadow-md">
        <span class="text-xs text-amber-200 font-semibold uppercase tracking-widest">Monitor de Cocina</span>
        <span class="text-xs bg-red-600 px-3 py-1 rounded-full font-bold animate-pulse text-white"><?= $totalActivas ?> Órdenes Activas</span>
    </div>

    <!-- Tablero de 4 Columnas -->
    <main class="flex-1 grid grid-cols-4 gap-4 p-4 overflow-hidden bg-gray-950">

        <!-- COLUMNA 1: EN ESPERA -->
        <div class="bg-gray-900/80 rounded-2xl p-3 border border-red-900/50 flex flex-col h-full shadow-inner">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-red-500/30">
                <h2 class="font-bold text-sm text-red-400 flex items-center space-x-1.5 uppercase tracking-wider"><span>🔴 En Espera</span></h2>
                <span class="bg-red-950 text-red-300 text-xs font-bold px-2 py-0.5 rounded-full border border-red-800"><?= count($ordenes['Pendiente']) ?></span>
            </div>
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                <?php foreach ($ordenes['Pendiente'] as $ord): ?>
                <div class="bg-gray-800 p-3 rounded-xl border border-red-500/60 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-extrabold text-sm text-red-400">#<?= htmlspecialchars($ord['codigo']) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mb-2">Hace <?= $ord['minutos'] ?> min</p>
                        <div class="bg-gray-900/90 p-2 rounded-lg text-xs space-y-1 mb-3">
                            <?php foreach ($ord['detalles'] as $det): ?>
                                <p class="font-bold text-gray-200"><?= $det['cantidad'] ?>x <?= htmlspecialchars($det['producto']) ?></p>
                                <?php if($det['extras']): ?>
                                    <p class="text-[10px] text-amber-300">★ <?= htmlspecialchars($det['extras']) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id_orden" value="<?= $ord['id_orden'] ?>">
                        <!-- Estado 2 = En preparación -->
                        <input type="hidden" name="nuevo_estado" value="2">
                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-1.5 rounded-lg text-xs transition">
                            Iniciar Preparación ➔
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- COLUMNA 2: EN PROCESO -->
        <div class="bg-gray-900/80 rounded-2xl p-3 border border-amber-900/50 flex flex-col h-full shadow-inner">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-amber-500/30">
                <h2 class="font-bold text-sm text-amber-400 flex items-center space-x-1.5 uppercase tracking-wider"><span>🟡 En Proceso</span></h2>
                <span class="bg-amber-950 text-amber-300 text-xs font-bold px-2 py-0.5 rounded-full border border-amber-800"><?= count($ordenes['En preparación']) ?></span>
            </div>
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                <?php foreach ($ordenes['En preparación'] as $ord): ?>
                <div class="bg-gray-800 p-3 rounded-xl border border-amber-500/60 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-extrabold text-sm text-amber-400">#<?= htmlspecialchars($ord['codigo']) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mb-2">Hace <?= $ord['minutos'] ?> min</p>
                        <div class="bg-gray-900/90 p-2 rounded-lg text-xs space-y-1 mb-3">
                            <?php foreach ($ord['detalles'] as $det): ?>
                                <p class="font-bold text-gray-200"><?= $det['cantidad'] ?>x <?= htmlspecialchars($det['producto']) ?></p>
                                <?php if($det['extras']): ?>
                                    <p class="text-[10px] text-amber-300">★ <?= htmlspecialchars($det['extras']) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id_orden" value="<?= $ord['id_orden'] ?>">
                        <!-- Estado 3 = Lista -->
                        <input type="hidden" name="nuevo_estado" value="3">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 rounded-lg text-xs transition">
                            Marcar como Lista ➔
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- COLUMNA 3: LISTAS -->
        <div class="bg-gray-900/80 rounded-2xl p-3 border border-emerald-900/50 flex flex-col h-full shadow-inner">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-emerald-500/30">
                <h2 class="font-bold text-sm text-emerald-400 flex items-center space-x-1.5 uppercase tracking-wider"><span>🟢 Listas</span></h2>
                <span class="bg-emerald-950 text-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full border border-emerald-800"><?= count($ordenes['Lista']) ?></span>
            </div>
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                <?php foreach ($ordenes['Lista'] as $ord): ?>
                <div class="bg-gray-800 p-3 rounded-xl border border-emerald-500/60 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-extrabold text-sm text-emerald-400">#<?= htmlspecialchars($ord['codigo']) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mb-2">Empacado • Hace <?= $ord['minutos'] ?> min</p>
                        <div class="bg-gray-900/90 p-2 rounded-lg text-xs space-y-1 mb-3">
                            <?php foreach ($ord['detalles'] as $det): ?>
                                <p class="font-bold text-gray-200"><?= $det['cantidad'] ?>x <?= htmlspecialchars($det['producto']) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id_orden" value="<?= $ord['id_orden'] ?>">
                        <!-- Estado 4 = Entregada -->
                        <input type="hidden" name="nuevo_estado" value="4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-lg text-xs transition">
                            Marcar Entregada ➔
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- COLUMNA 4: ENTREGADAS -->
        <div class="bg-gray-900/80 rounded-2xl p-3 border border-gray-800 flex flex-col h-full shadow-inner opacity-80">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-700/50">
                <h2 class="font-bold text-sm text-gray-400 flex items-center space-x-1.5 uppercase tracking-wider"><span>⚪ Entregadas</span></h2>
                <span class="bg-gray-800 text-gray-400 text-xs font-bold px-2 py-0.5 rounded-full border border-gray-700"><?= count($ordenes['Entregada']) ?></span>
            </div>
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                <?php foreach ($ordenes['Entregada'] as $ord): ?>
                <div class="bg-gray-800/80 p-3 rounded-xl border border-gray-700 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-extrabold text-sm text-gray-300">#<?= htmlspecialchars($ord['codigo']) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mb-2">Finalizada hace <?= $ord['minutos'] ?> min</p>
                    </div>
                    <button class="w-full bg-gray-700 font-bold py-1.5 rounded-lg text-xs text-gray-400 cursor-default" disabled>
                        ✓ Finalizada
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>