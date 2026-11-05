document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-anulacion');
    if (!modal) return;

    const $ = (id) => document.getElementById(id);
    const form = $('form-anulacion');
    const inputId = $('anul-id');
    const resumen = $('anul-resumen');
    const motivo = $('anul-motivo');
    const bloqueReembolso = $('anul-bloque-reembolso');
    const avisoReembolso = $('anul-aviso-reembolso');
    const chkReembolso = $('anul-reembolso');
    const btnConfirmar = $('btn-confirmar-anulacion');

    function abrir(btn) {
        inputId.value = btn.dataset.id;
        resumen.textContent = `${btn.dataset.codigo} · ${btn.dataset.cliente} · $${btn.dataset.total}`;
        motivo.value = '';
        btnConfirmar.disabled = false;

        // Si ya se cobró, hay que confirmar la devolución del dinero
        const pagado = parseFloat(btn.dataset.pagado || '0');
        const estaPagada = pagado > 0;
        bloqueReembolso.classList.toggle('hidden', !estaPagada);
        chkReembolso.checked = false;
        chkReembolso.required = estaPagada;   // solo es obligatorio cuando se ve
        if (estaPagada) {
            avisoReembolso.textContent = `Esta orden ya fue cobrada ($${pagado.toFixed(2)} en ${btn.dataset.metodo || 'caja'}). Devuelve el dinero al cliente antes de anularla.`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => motivo.focus(), 50);
    }

    function cerrar() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-anular');
        if (btn) abrir(btn);
    });
    $('btn-cerrar-anulacion').addEventListener('click', cerrar);
    $('btn-cancelar-anulacion').addEventListener('click', cerrar);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) cerrar();
    });

    // El navegador valida (motivo mínimo 5 caracteres, checkbox si aplica); al enviar se evita el doble clic
    form.addEventListener('submit', () => { btnConfirmar.disabled = true; });
});
