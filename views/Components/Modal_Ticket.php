<!-- Modal: Vista previa del ticket (se muestra al procesar una orden o desde "Ver ticket"). El botón abre el PDF. -->
<style>
    /* Papel de ticket térmico de 80 mm */
    #ticket-imprimible { width: 302px; font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.35; color: #000; background: #fff; }
    #ticket-imprimible .t-centro { text-align: center; }
    #ticket-imprimible .t-linea { border-top: 1px dashed #000; margin: 6px 0; }
    #ticket-imprimible .t-fila { display: flex; justify-content: space-between; gap: 8px; }
    #ticket-imprimible .t-extra { padding-left: 14px; font-size: 11px; }
    #ticket-imprimible .t-grande { font-size: 15px; font-weight: bold; }
</style>

<div id="modal-ticket" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-[70] p-4">
    <div class="bg-[#E5E7EB] rounded-2xl shadow-2xl border-2 border-[#D4AF37] w-full max-w-sm max-h-[92vh] flex flex-col">

        <div class="bg-[#1B4332] px-4 py-3 text-center border-b-2 border-[#D4AF37] rounded-t-2xl relative shrink-0">
            <h3 class="text-base font-bold text-[#D4AF37] tracking-wider" style="font-family: 'Cinzel', serif;">TICKET DE ORDEN</h3>
            <button type="button" id="btn-cerrar-ticket-x" class="absolute top-3 right-3 text-gray-300 hover:text-white text-lg font-bold cursor-pointer" aria-label="Cerrar">✕</button>
        </div>

        <!-- Vista previa del ticket (lo que se imprime) -->
        <div class="p-4 overflow-y-auto flex justify-center">
            <div id="ticket-imprimible" class="shadow-lg p-3">
                <p class="t-centro">Cargando ticket...</p>
            </div>
        </div>

        <div class="p-4 pt-0 flex gap-2 shrink-0">
            <button type="button" id="btn-cerrar-ticket" class="w-1/3 bg-white hover:bg-gray-100 text-gray-700 font-bold py-3 rounded-xl border border-gray-300 transition text-xs cursor-pointer">
                Cerrar
            </button>
            <button type="button" id="btn-imprimir-ticket" disabled class="w-2/3 bg-[#1B4332] hover:bg-[#14281E] text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                 Abrir PDF para imprimir
            </button>
        </div>
    </div>
</div>
