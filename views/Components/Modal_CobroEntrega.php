<!-- Modal de Cobro y Confirmación de Entrega -->
<div id="modal-cobro-entrega" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border-2 border-[#D4AF37] animate-fade-in">
        
        <!-- Header del Modal -->
        <div class="bg-[#1B4332] p-4 text-center border-b-2 border-[#D4AF37] relative">
            <h3 class="text-lg font-bold text-[#D4AF37] tracking-wider" style="font-family: 'Cinzel', serif;">COBRO Y ENTREGA DE ORDEN</h3>
            <p id="modal-orden-codigo" class="text-xs text-amber-200 font-semibold uppercase tracking-widest mt-0.5">#ORD-0000</p>
            <button onclick="cerrarModalCobro()" class="absolute top-3 right-3 text-gray-300 hover:text-white text-lg font-bold">✕</button>
        </div>

        <!-- Cuerpo del Modal -->
        <div class="p-5 space-y-4">
            
            <!-- Datos del Cliente y Resumen -->
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-medium">Cliente:</span>
                    <span id="modal-cliente-nombre" class="font-bold text-gray-900 uppercase">Juan Pérez</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-medium">Estado Actual de Pago:</span>
                    <span class="bg-amber-100 text-amber-800 border border-amber-300 font-bold px-2 py-0.5 rounded text-[10px]">⚠️ PENDIENTE DE PAGO</span>
                </div>
            </div>

            <!-- Detalle de Productos -->
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Detalle de la Orden:</span>
                <div id="modal-lista-productos" class="bg-gray-900 text-gray-200 p-3 rounded-xl text-xs space-y-1 max-h-32 overflow-y-auto border border-gray-800">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>

            <!-- Total a Pagar -->
            <div class="flex justify-between items-center p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                <span class="text-xs font-bold text-emerald-900">Monto Total a Cobrar:</span>
                <span id="modal-total-cobrar" class="text-2xl font-extrabold text-[#1B4332]">$0.00</span>
            </div>

            <!-- Selector de Forma de Pago -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">💳 Selecciona la Forma de Pago:</label>
                <select id="modal_id_metodo_pago" class="w-full bg-white border-2 border-[#1B4332] rounded-xl p-2.5 text-xs font-bold text-gray-800 focus:outline-none">
                    <option value="1">💵 Efectivo</option>
                    <option value="2">💳 Tarjeta de Crédito / Débito</option>
                    <option value="3">📱 Transferencia / Chivo Wallet</option>
                </select>
            </div>

            <input type="hidden" id="modal_id_orden_val">

            <!-- Botón de Confirmación -->
            <button id="btn-confirmar-cobro-entrega" class="w-full bg-[#1B4332] hover:bg-emerald-900 border border-[#D4AF37] text-white font-bold py-3 rounded-xl shadow-lg transition text-xs cursor-pointer">
                ✓ Registrar Pago y Entregar Orden
            </button>
        </div>

    </div>
</div>