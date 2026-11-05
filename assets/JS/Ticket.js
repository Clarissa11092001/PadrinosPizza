// Ticket de orden: la vista previa se arma con los datos GUARDADOS en la base de datos
// (controllers/TicketController.php) y el PDF para imprimir lo genera mPDF
// (controllers/TicketPdfController.php).
(function () {
    const URL_TICKET = "/Padrino's_Pizza/controllers/TicketController.php?id=";
    const URL_PDF = "/Padrino's_Pizza/controllers/TicketPdfController.php?id=";
    let idActual = null; // orden cuyo ticket se está mostrando

    const esc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = (n) => '$' + Number(n || 0).toFixed(2);
    const fila = (izq, der) => `<div class="t-fila"><span>${izq}</span><span>${der}</span></div>`;

    function construirHtml(t) {
        let h = '';
        h += `<div class="t-centro t-grande">PADRINO'S PIZZA</div>`;
        h += `<div class="t-centro">Ticket de orden</div>`;
        h += `<div class="t-linea"></div>`;
        h += `<div class="t-centro t-grande">${esc(t.codigo)}</div>`;
        h += `<div class="t-centro">${esc(t.fecha)}</div>`;
        h += `<div class="t-linea"></div>`;
        h += fila('Cliente:', esc(t.cliente));
        if (t.hora_entrega) h += fila('Hora solicitada:', esc(t.hora_entrega));
        if (t.cajero) h += fila('Atendió:', esc(t.cajero));
        h += `<div class="t-linea"></div>`;

        (t.items || []).forEach((it) => {
            h += fila(`${it.cantidad}x ${esc(it.nombre)}`, money(it.precio_base * it.cantidad));
            (it.extras || []).forEach((ex) => {
                h += `<div class="t-fila t-extra"><span>+ ${esc(ex.nombre)}</span><span>${money(ex.precio)}</span></div>`;
            });
        });

        h += `<div class="t-linea"></div>`;
        h += fila('Subtotal:', money(t.subtotal));
        if (t.descuento > 0) h += fila('Descuento:', '-' + money(t.descuento));
        h += `<div class="t-fila t-grande"><span>TOTAL:</span><span>${money(t.total)}</span></div>`;
        h += `<div class="t-linea"></div>`;

        if (t.pagado) {
            h += `<div class="t-centro t-grande">*** PAGADO ***</div>`;
            h += `<div class="t-centro">${esc(t.metodo_pago)} · ${money(t.monto_pagado)}</div>`;
            if (t.fecha_pago) h += `<div class="t-centro">${esc(t.fecha_pago)}</div>`;
        } else {
            h += `<div class="t-centro t-grande">*** POR CANCELAR ***</div>`;
            h += `<div class="t-centro">Pago pendiente: cobrar al entregar</div>`;
        }

        if (t.observaciones) {
            h += `<div class="t-linea"></div>`;
            h += `<div><strong>Detalles:</strong> ${esc(t.observaciones)}</div>`;
        }

        h += `<div class="t-linea"></div>`;
        h += `<div class="t-centro">¡Gracias por preferirnos!</div>`;
        return h;
    }

    function elementos() {
        return {
            modal: document.getElementById('modal-ticket'),
            papel: document.getElementById('ticket-imprimible'),
            btnImprimir: document.getElementById('btn-imprimir-ticket'),
        };
    }

    function cerrar() {
        const { modal } = elementos();
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Abre el modal y carga el ticket de la orden. Devuelve una promesa: true si se pudo mostrar.
    async function abrir(idOrden) {
        const { modal, papel, btnImprimir } = elementos();
        if (!modal) return false;

        idActual = idOrden;
        papel.innerHTML = '<p class="t-centro">Generando ticket...</p>';
        btnImprimir.disabled = true;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const res = await fetch(URL_TICKET + encodeURIComponent(idOrden), { credentials: 'same-origin' });
            const texto = await res.text();
            let data;
            try { data = JSON.parse(texto); }
            catch (e) { console.error('Respuesta no válida del ticket:', texto); throw new Error('Respuesta inesperada del servidor.'); }
            if (!data.success) throw new Error(data.error || 'No se pudo generar el ticket.');

            papel.innerHTML = construirHtml(data.ticket);
            btnImprimir.disabled = false;
            return true;
        } catch (err) {
            console.error(err);
            papel.innerHTML = `<p class="t-centro">⚠️ ${esc(err.message)}</p>`;
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const b = (id) => document.getElementById(id);
        if (b('btn-cerrar-ticket')) b('btn-cerrar-ticket').addEventListener('click', cerrar);
        if (b('btn-cerrar-ticket-x')) b('btn-cerrar-ticket-x').addEventListener('click', cerrar);
        // El PDF se abre en otra pestaña: ahí se imprime o se descarga con los botones del visor
        if (b('btn-imprimir-ticket')) b('btn-imprimir-ticket').addEventListener('click', () => {
            if (idActual) window.open(URL_PDF + encodeURIComponent(idActual), '_blank');
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrar(); });

        // Botones "Ver ticket" (pantalla de Entregas): <button class="btn-ver-ticket" data-id="..">
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-ver-ticket');
            if (btn) abrir(btn.dataset.id);
        });
    });

    window.Ticket = { abrir, cerrar };
})();
