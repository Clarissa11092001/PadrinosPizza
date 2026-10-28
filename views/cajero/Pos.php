<?php
// Configurar clases dinámicas para que el layout ocupe toda la pantalla
$bodyClasses = 'bg-gray-100 font-sans h-screen flex flex-col';
$extraCss = '/Padrino\'s_Pizza/assets/css/Pos.css';
require_once __DIR__ . '/../../includes/header.php';

/**
 * Variables inyectadas por el controlador:
 * @var array $categorias
 * @var array $productos
 * @var array $extrasPorProducto
 */
?>
    <!-- Contenido Principal POS -->
    <div class="flex-1 flex overflow-hidden">

        <!-- PANEL IZQUIERDO: Catálogo de Productos -->
        <main class="flex-1 p-5 overflow-y-auto">
            
            <!-- Categorías Dinámicas -->
            <div class="mb-6 flex space-x-3 overflow-x-auto pb-2" id="contenedor-categorias">
                <button class="btn-categoria activo bg-[#1B4332] text-white px-5 py-2 rounded-xl font-bold text-xs shadow flex items-center space-x-2 shrink-0 cursor-pointer" data-id="0">
                    <span>Todas</span>
                </button>
                
                <?php if (isset($categorias) && is_array($categorias)): foreach ($categorias as $cat): ?>
                <button class="btn-categoria bg-white text-gray-700 px-5 py-2 rounded-xl font-bold text-xs shadow hover:bg-gray-50 flex items-center space-x-2 border border-gray-200 shrink-0 cursor-pointer" data-id="<?= $cat['id_categoria'] ?>">
                    <span><?= htmlspecialchars($cat['nombre']) ?></span>
                </button>
                <?php endforeach; endif; ?>
            </div>

            <!-- Rejilla de Tarjetas de Productos (4 Columnas) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 items-start">
                
                <?php if (isset($productos) && is_array($productos)): foreach ($productos as $prod): 
                    $idCat = intval($prod['id_categoria']);
                    $borderBgClass = match($idCat) {
                        1 => 'border-t-4 border-t-red-600',          // Pizzas (Rojo)
                        2 => 'border-t-4 border-t-blue-500',         // Bebidas (Azul)
                        3 => 'border-t-4 border-t-amber-500',        // Entradas (Dorado)
                        default => 'border-t-4 border-t-emerald-700' // General
                    };
                    $tieneExtras = isset($extrasPorProducto[$prod['id_producto']]) && !empty($extrasPorProducto[$prod['id_producto']]);
                ?>
                <div class="tarjeta-producto bg-white p-4 rounded-2xl shadow-sm border border-gray-200 <?= $borderBgClass ?> flex flex-col justify-between" data-categoria="<?= $prod['id_categoria'] ?>">
                    <div>
                        <!-- Titulo y Precio -->
                        <div class="flex justify-between items-start mb-1 gap-2">
                            <h3 class="font-bold text-sm text-gray-900 leading-tight flex-1"><?= htmlspecialchars($prod['nombre']) ?></h3>
                            <span class="text-sm font-extrabold text-[#1B4332] shrink-0">$<?= number_format($prod['precio'], 2) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-500 mb-3 line-clamp-2"><?= htmlspecialchars($prod['descripcion']) ?></p>
                        
                        <!-- Sección de Ingredientes / Extras (Solo si la categoría los requiere) -->
                        <?php if ($tieneExtras): ?>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 mb-3">
                            <span class="text-[9px] font-bold text-gray-600 block mb-1.5 uppercase tracking-wider">Ingredientes / Extras:</span>
                            <div class="space-y-1.5 max-h-28 overflow-y-auto pr-1">
                                <?php foreach ($extrasPorProducto[$prod['id_producto']] as $extra): ?>
                                <label class="flex items-center justify-between text-[11px] text-gray-700 cursor-pointer hover:bg-gray-100 p-1 rounded transition">
                                    <div class="flex items-center space-x-1.5 truncate">
                                        <input type="checkbox" class="extra-checkbox rounded text-[#1B4332] focus:ring-emerald-800 h-3.5 w-3.5 cursor-pointer"
                                               data-id="<?= $extra['id_extra'] ?>"
                                               data-nombre="<?= htmlspecialchars($extra['nombre']) ?>"
                                               data-precio="<?= $extra['precio'] ?>">
                                        <span class="truncate"><?= htmlspecialchars($extra['nombre']) ?></span>
                                    </div>
                                    <span class="font-semibold text-gray-500 text-[10px] shrink-0 ml-1">+$<?= number_format($extra['precio'], 2) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <button class="btn-agregar w-full bg-[#1B4332] hover:bg-emerald-950 text-white font-bold py-2 rounded-xl text-xs transition shadow cursor-pointer mt-2"
                            data-id="<?= $prod['id_producto'] ?>"
                            data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                            data-precio="<?= $prod['precio'] ?>">
                        + Agregar a la Orden
                    </button>
                </div>
                <?php endforeach; endif; ?>

            </div>

        </main>

        <!-- PANEL DERECHO: Resumen de la Orden -->
        <aside class="w-96 bg-white border-l border-gray-200 p-4 flex flex-col justify-between shadow-xl">
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Cabecera del Ticket -->
                <div class="flex justify-between items-center border-b pb-2 mb-3">
                    <h2 class="text-lg font-extrabold text-gray-900" style="font-family: 'Cinzel', serif;">NUEVA ORDEN</h2>
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full">Mostrador</span>
                </div>
                
                <!-- Lista de Productos Añadidos -->
                <div id="lista-ticket" class="space-y-3 flex-1 overflow-y-auto pr-1">
                    <!-- Items del carrito -->
                </div>
            </div>

            <!-- Sección Inferior: Nombre del Cliente, Hora, Observaciones y Pago -->
            <div class="pt-3 border-t border-gray-100 mt-2 space-y-3">
                
                <!-- Datos de la Entrega (Cliente + Hora + Notas) -->
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-2.5 text-xs">
                    
                    <!-- Campo Nombre del Cliente -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">👤 Nombre del Cliente *:</label>
                        <input type="text" id="nombre_cliente" required placeholder="Ej. Juan Pérez" 
                               class="w-full bg-white border border-gray-300 rounded-lg p-1.5 text-xs text-gray-800 focus:outline-none focus:ring-1 focus:ring-[#1B4332]">
                    </div>

                    <!-- Campo Hora Solicitada -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">⏰ Hora Solicitada (07:00 AM - 08:30 PM):</label>
                        <input type="time" id="hora_entrega" min="07:00" max="20:30" 
                               class="w-full bg-white border border-gray-300 rounded-lg p-1.5 text-xs text-gray-800 focus:outline-none focus:ring-1 focus:ring-[#1B4332]">
                    </div>

                    <!-- Observaciones para Cocina -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">📝 Observaciones para Cocina:</label>
                        <textarea id="observaciones_orden" rows="1" placeholder="Ej. Bien cocida, sin cebolla..." 
                                  class="w-full bg-white border border-gray-300 rounded-lg p-1.5 text-xs text-gray-800 focus:outline-none focus:ring-1 focus:ring-[#1B4332]"></textarea>
                    </div>
                </div>

                <!-- Totales -->
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Subtotal:</span>
                        <span id="ticket-subtotal" class="font-bold text-gray-800">$0.00</span>
                    </div>
                    
                    <div class="flex justify-between text-lg font-extrabold text-gray-900">
                        <span>Total a Pagar:</span>
                        <span id="ticket-total" class="text-xl text-[#1B4332]">$0.00</span>
                    </div>
                </div>

                <!-- Botones de Estado de Pago -->
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="btn-pago-realizado" class="estado-pago activo bg-emerald-100 border-2 border-emerald-600 py-2 rounded-xl text-xs font-bold text-emerald-900 text-center cursor-pointer" data-estado="pagado">
                        Cancelada (Pagado)
                    </button>
                    <button type="button" id="btn-pago-pendiente" class="estado-pago border border-gray-300 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 text-center cursor-pointer" data-estado="pendiente">
                        Por Cancelar
                    </button>
                    <input type="hidden" id="estado_pago_seleccionado" value="pagado">
                </div>

                <!-- Botón Principal -->
                <button id="btn-procesar" disabled class="w-full bg-[#A61C1C] hover:bg-red-800 text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    Procesar e Imprimir Orden
                </button>
            </div>
        </aside>

    </div>

<!-- Componentes Modales -->
<?php require_once __DIR__ . '/../Components/Modal_Apertura.php'; ?>
<?php require_once __DIR__ . '/../Components/Modal_Cierre.php'; ?>

<!-- Scripts de JavaScript -->
<script src="/Padrino's_Pizza/assets/js/ValidarPos.js"></script>
<script src="/Padrino's_Pizza/assets/js/Cierre_Caja.js"></script>

<?php
$extraJs = '/Padrino\'s_Pizza/assets/js/Pos.js';
require_once __DIR__ . '/../../includes/footer.php';
?>
