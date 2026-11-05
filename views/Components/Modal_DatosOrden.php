<!-- Modal: Datos del pedido (se abre con "Siguiente") -->
<div id="modal-datos-orden" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border-2 border-[#D4AF37]">

        <div class="bg-[#1B4332] p-4 text-center border-b-2 border-[#D4AF37] relative rounded-t-2xl">
            <h3 class="text-lg font-bold text-[#D4AF37] tracking-wider" style="font-family: 'Cinzel', serif;">DATOS DEL PEDIDO</h3>
            <p id="modal-datos-resumen" class="text-xs text-amber-200 font-semibold mt-0.5"></p>
            <button type="button" id="btn-cerrar-modal-datos" class="absolute top-3 right-3 text-gray-300 hover:text-white text-lg font-bold cursor-pointer" aria-label="Cerrar">✕</button>
        </div>

        <form id="form-datos-orden" novalidate class="p-5 space-y-4">
            <div>
                <label for="nombre_cliente" class="block text-xs font-bold text-gray-700 mb-1">👤 Nombre del Cliente *</label>
                <input type="text" id="nombre_cliente" autocomplete="off" maxlength="100" placeholder="Ej. Juan Pérez"
                       class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1B4332]">
                <p class="error-campo hidden text-[11px] text-red-600 font-semibold mt-1" data-para="nombre_cliente"></p>
            </div>

            <div>
                <label for="hora_entrega" class="block text-xs font-bold text-gray-700 mb-1">⏰ Hora Solicitada * (07:00 AM - 08:30 PM)</label>
                <input type="text" id="hora_entrega" autocomplete="off" placeholder="Selecciona la hora"
                       class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1B4332] cursor-pointer">
                <p class="error-campo hidden text-[11px] text-red-600 font-semibold mt-1" data-para="hora_entrega"></p>
            </div>

            <div>
                <label for="observaciones_orden" class="block text-xs font-bold text-gray-700 mb-1">📝 Detalles para Cocina <span class="font-normal text-gray-400">(opcional)</span></label>
                <textarea id="observaciones_orden" rows="2" maxlength="255" placeholder="Ej. Bien cocida, sin cebolla..."
                          class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1B4332]"></textarea>
            </div>

            <p id="error-general-orden" class="hidden bg-red-50 border border-red-300 text-red-700 text-xs font-semibold rounded-lg p-2.5"></p>

            <div class="flex gap-2 pt-1">
                <button type="button" id="btn-volver-modal-datos" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-xl transition text-xs cursor-pointer">
                    ← Volver
                </button>
                <button type="submit" id="btn-procesar" class="w-2/3 bg-[#A61C1C] hover:bg-red-800 text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    Procesar Orden
                </button>
            </div>
        </form>
    </div>
</div>
