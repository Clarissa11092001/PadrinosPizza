<!-- Modal: Anular orden (gerencia). Pide motivo obligatorio y, si la orden ya se cobró, confirmar la devolución -->
<div id="modal-anulacion" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-[#14281E] w-full max-w-md rounded-2xl shadow-2xl border-2 border-[#A61C1C]">

        <div class="bg-[#7F1D1D] p-4 text-center rounded-t-2xl relative">
            <h3 class="text-lg font-bold text-white tracking-wider" style="font-family: 'Cinzel', serif;">ANULAR ORDEN</h3>
            <p id="anul-resumen" class="text-xs text-red-100 font-semibold mt-0.5"></p>
            <button type="button" id="btn-cerrar-anulacion" class="absolute top-3 right-3 text-red-100 hover:text-white text-lg font-bold cursor-pointer" aria-label="Cerrar">✕</button>
        </div>

        <form id="form-anulacion" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="anular_id" id="anul-id" value="">

            <div>
                <label for="anul-motivo" class="block text-xs font-bold text-gray-300 mb-1">Motivo de la anulación *</label>
                <textarea id="anul-motivo" name="motivo_anulacion" rows="3" required minlength="5" maxlength="200"
                          placeholder="Ej. El cliente canceló el pedido / error al tomar la orden"
                          class="w-full bg-[#0E1A14] border border-[#D4AF37]/40 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]"></textarea>
                <p class="text-[11px] text-gray-500 mt-1">Queda registrado con tu usuario y la fecha. La orden no se borra.</p>
            </div>

            <!-- Solo aparece si la orden ya fue cobrada -->
            <div id="anul-bloque-reembolso" class="hidden bg-amber-900/30 border border-amber-500 rounded-lg p-3">
                <p id="anul-aviso-reembolso" class="text-xs text-amber-200 font-semibold mb-2"></p>
                <label class="flex items-start gap-2 text-xs text-amber-100 cursor-pointer">
                    <input type="checkbox" id="anul-reembolso" name="reembolso_confirmado" value="1" class="mt-0.5">
                    <span>Confirmo que el dinero ya fue devuelto al cliente.</span>
                </label>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" id="btn-cancelar-anulacion" class="w-1/3 bg-white/10 hover:bg-white/20 text-gray-200 font-bold py-3 rounded-xl transition text-xs cursor-pointer">
                    Volver
                </button>
                <button type="submit" id="btn-confirmar-anulacion" class="w-2/3 bg-[#A61C1C] hover:bg-red-800 text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    Anular orden
                </button>
            </div>
        </form>
    </div>
</div>
