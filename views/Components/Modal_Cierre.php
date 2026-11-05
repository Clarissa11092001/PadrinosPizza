<!-- Modal Emergente para el Arqueo de Caja -->
<div id="modal-cierre" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50">
  <div class="bg-[#14281E] border border-[#D4AF37] rounded-2xl p-6 max-w-lg w-full shadow-2xl text-white">
    
    <!-- Cabecera del Modal con Botón X -->
    <div class="flex justify-between items-center mb-4 border-b border-[#D4AF37]/30 pb-2">
      <h3 class="text-lg font-bold text-[#D4AF37] font-padrino">🔒 Arqueo de Caja (Conteo Físico)</h3>
      <button type="button" onclick="cerrarModalCierre()" class="text-gray-400 hover:text-white font-bold text-lg p-1">✕</button>
    </div>

    <!-- Resumen de Ventas de la Caja -->
    <div class="space-y-2 text-xs mb-4 bg-black/30 p-4 rounded-xl border border-white/5">
      <div class="flex justify-between"><span class="text-gray-400">Monto Base Inicial:</span><span id="cierre-base" class="font-bold">$0.00</span></div>
      <div class="flex justify-between"><span class="text-gray-400">Ventas Registradas en POS:</span><span id="cierre-ventas" class="font-bold text-emerald-400">$0.00</span></div>
      <div class="flex justify-between text-sm font-extrabold border-t border-white/10 pt-2"><span class="text-[#D4AF37]">Total Esperado en Gaveta:</span><span id="cierre-esperado" class="text-[#D4AF37]">$0.00</span></div>
    </div>

    <!-- Formulario de Declaración Física -->
    <form id="form-cierre" onsubmit="procesarCierreYSalir(event)" class="space-y-4">
      <div>
        <label class="block text-xs text-gray-400 mb-1">Efectivo Contado Físicamente ($)</label>
        <input type="number" step="0.01" id="monto_declarado_input" required placeholder="0.00" 
               class="w-full bg-black/40 border border-[#D4AF37]/50 rounded-xl p-3 text-center text-2xl font-bold text-emerald-400 focus:outline-none">
      </div>
      
      <div>
        <label class="block text-xs text-gray-400 mb-1">Observaciones / Justificación de Descuadre</label>
        <textarea id="observaciones_input" rows="2" placeholder="Si hay sobrante o faltante, explica la razón aquí..." class="w-full bg-black/40 border border-[#D4AF37]/50 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#D4AF37]"></textarea>
      </div>
      
      <!-- Botones de Acción -->
      <div class="flex gap-2">
        <button type="button" onclick="cerrarSesionSinCierre()" class="w-1/3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-3 rounded-xl transition text-xs">
          Solo Salir
        </button>
        <button type="submit" class="w-2/3 bg-[#A61C1C] hover:bg-red-800 text-white font-bold py-3 rounded-xl transition text-sm shadow">
          Cerrar Caja y Salir
        </button>
      </div>
    </form>
  </div>
</div>