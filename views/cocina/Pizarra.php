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

    <!-- Franja Informativa KDS con colores corporativos -->
    <div class="bg-[#1B4332] p-2 px-5 border-b border-[#D4AF37]/40 flex justify-center items-center shadow-md shrink-0">
        <span class="text-xs bg-red-600 px-3 py-1 rounded-full font-bold animate-pulse text-white"><?= $totalActivas ?> Órdenes Activas</span>
    </div>

    <!-- Tablero de 3 Columnas con Scroll Individual -->
    <main class="flex-1 grid grid-cols-3 gap-4 p-4 overflow-hidden bg-white">

        <?php 
        $columnasConfig = [
            'Pendiente'      => ['titulo' => '🔴 En Espera', 'color' => 'red', 'btnText' => 'Iniciar Preparación ➔', 'nuevoEstado' => 2, 'esReversa' => false],
            'En preparación' => ['titulo' => '🟡 En Proceso', 'color' => 'amber', 'btnText' => 'Marcar como Lista ➔', 'nuevoEstado' => 3, 'esReversa' => false],
            'Lista'          => ['titulo' => '🟢 Listas', 'color' => 'emerald', 'btnText' => '↩ Regresar a En Proceso', 'nuevoEstado' => 2, 'esReversa' => true]
        ];

        foreach ($columnasConfig as $estadoNombre => $config): 
            $c = $config['color'];
            $listaOrdenes = $ordenes[$estadoNombre] ?? [];
        ?>

        <!-- COLUMNA: <?= $estadoNombre ?> -->
        <div class="bg-gray-900/80 rounded-2xl p-3 border border-<?= $c ?>-900/50 flex flex-col h-full shadow-inner overflow-hidden">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-<?= $c ?>-500/30 shrink-0">
                <h2 class="font-bold text-sm text-<?= $c ?>-400 flex items-center space-x-1.5 uppercase tracking-wider"><span><?= $config['titulo'] ?></span></h2>
                <span class="bg-<?= $c ?>-950 text-<?= $c ?>-300 text-xs font-bold px-2 py-0.5 rounded-full border border-<?= $c ?>-800"><?= count($listaOrdenes) ?></span>
            </div>

            <!-- Scroll por columna -->
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                <?php foreach ($listaOrdenes as $ord): ?>
                <div class="bg-gray-800 p-3 rounded-xl border border-<?= $c ?>-500/60 shadow-md flex flex-col justify-between space-y-2">
                    <div>
                        <!-- Código de Orden + Cliente -->
                        <div class="flex justify-between items-start mb-1">
                            <div>
                                <span class="font-extrabold text-sm text-<?= $c ?>-400">#<?= htmlspecialchars($ord['codigo']) ?></span>
                                <h4 class="font-bold text-xs text-white uppercase tracking-wide truncate max-w-[130px]"><?= htmlspecialchars($ord['cliente']) ?></h4>
                            </div>
                            
                            <!-- Banner de Hora Solicitada -->
                            <?php if ($ord['hora_entrega']): ?>
                                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/50 text-[10px] px-2 py-0.5 rounded-md font-extrabold shadow">
                                    ⏰ <?= $ord['hora_entrega'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-[10px] text-gray-400 mb-2">Hace <?= $ord['minutos'] ?> min</p>

                        <!-- Lista de Productos -->
                        <div class="bg-gray-900/90 p-2.5 rounded-lg text-xs space-y-1.5 mb-2 border border-gray-700/40">
                            <?php foreach ($ord['detalles'] as $det): ?>
                                <div>
                                    <p class="font-bold text-gray-200"><?= $det['cantidad'] ?>x <?= htmlspecialchars($det['producto']) ?></p>
                                    <?php if($det['extras']): ?>
                                        <p class="text-[10px] text-amber-300 font-semibold pl-2">★ Extra: <?= htmlspecialchars($det['extras']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Observaciones de la Orden -->
                        <?php if (!empty($ord['notas_orden'])): ?>
                            <div class="bg-red-950/40 border border-red-500/40 p-2 rounded-lg text-[11px] text-red-200 font-medium mb-2">
                                📝 <span class="underline font-bold">Nota:</span> <?= htmlspecialchars($ord['notas_orden']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Acciones según estado -->
                    <?php if ($config['nuevoEstado'] > 0): ?>
                        <form method="POST" onsubmit="return <?= $config['esReversa'] ? "confirm('⚠️ ¿Estás seguro de regresar la orden #" . htmlspecialchars($ord['codigo']) . " a \\'En Proceso\\'?');" : "true" ?>;">
                            <input type="hidden" name="id_orden" value="<?= $ord['id_orden'] ?>">
                            <input type="hidden" name="nuevo_estado" value="<?= $config['nuevoEstado'] ?>">
                            <button type="submit" 
                                    class="w-full font-bold py-1.5 rounded-lg text-xs transition cursor-pointer <?= $config['esReversa'] ? 'bg-amber-700 hover:bg-amber-800 text-white border border-amber-500/50' : 'bg-' . ($c === 'red' ? 'amber' : 'emerald') . '-600 hover:opacity-90 text-white' ?>">
                                <?= $config['btnText'] ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Alerta de día anterior -->
                    <?php if (!empty($ord['es_dia_anterior'])): ?>
                        <span class="bg-red-500 text-white font-extrabold text-[9px] px-2 py-0.5 rounded-md tracking-wider uppercase animate-bounce border border-red-300">
                            ⚠️ Rezagada (Ayer)
                        </span>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </main>

<?php
$extraJs = '/Padrino\'s_Pizza/assets/js/Pizarra.js';
?>
<script src="/Padrino's_Pizza/assets/JS/RefrescarPizarras.js"></script>
<?php
require_once __DIR__ . '/../../includes/footer.php';
?>