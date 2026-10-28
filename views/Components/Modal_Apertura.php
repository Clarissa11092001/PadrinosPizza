<!-- Modal Emergente para la Apertura de Turno de Caja -->
<div id="modal-apertura" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50">
  <div class="bg-[#14281E] border border-[#D4AF37] rounded-2xl p-6 max-w-md w-full shadow-2xl text-white">
    <div class="flex justify-between items-center mb-4 border-b border-[#D4AF37]/30 pb-2">
      <h3 class="text-xl font-bold text-[#D4AF37] font-padrino">🔓 Apertura de Turno</h3>
      <button type="button" onclick="cerrarModalApertura()" class="text-gray-400 hover:text-white font-bold text-lg p-1">✕</button>
    </div>

    <p class="text-xs text-gray-300 mb-4">Ingresa el saldo inicial disponible en la gaveta en efectivo para iniciar el turno operativo.</p>
    
    <form id="form-apertura" onsubmit="procesarApertura(event)" class="space-y-4">
    <div>
  <label class="block text-xs text-gray-400 mb-1">Monto Inicial Base Fijo ($)</label>
  <input type="number" step="0.01" id="monto_inicial_input" value="50.00" readonly 
         class="w-full bg-black/60 border border-[#D4AF37]/50 rounded-xl p-3 text-center text-2xl font-bold text-[#D4AF37] cursor-not-allowed">
  <p class="text-[10px] text-gray-400 mt-1 text-center">Fondo base asignado por Gerencia para cambio inicial.</p>
</div>
      
      <div class="flex gap-2">
        <button type="button" onclick="cerrarSesionSinCierre()" class="w-1/3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-3 rounded-xl transition text-xs">
          Salir
        </button>
        <button type="submit" class="w-2/3 bg-[#1B4332] hover:bg-emerald-800 border border-[#D4AF37] text-white font-bold py-3 rounded-xl transition text-sm shadow">
          Abrir Caja e Iniciar
        </button>
      </div>
    </form>
  </div>
</div>