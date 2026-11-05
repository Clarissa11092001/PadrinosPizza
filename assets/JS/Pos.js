document.addEventListener('DOMContentLoaded', () => {
    const URL_POS = "/Padrino's_Pizza/controllers/PosController.php";
    const HORA_MIN = 7 * 60;        // 07:00 AM
    const HORA_MAX = 20 * 60 + 30;  // 08:30 PM

    let carrito = [];
    let enviando = false;

    const $ = (id) => document.getElementById(id);

    // Carrito
    const listaTicket = $('lista-ticket');
    const ticketSubtotal = $('ticket-subtotal');
    const ticketTotal = $('ticket-total');
    const btnSiguiente = $('btn-siguiente');

    // Pago (se queda en el panel del carrito)
    const btnPagado = $('btn-pago-realizado');
    const btnPendiente = $('btn-pago-pendiente');
    const inputEstado = $('estado_pago_seleccionado');
    const contenedorMetodo = $('contenedor-metodo-pago');
    const selectMetodoPago = $('id_metodo_pago');

    // Modal de datos del pedido
    const modalDatos = $('modal-datos-orden');
    const formDatos = $('form-datos-orden');
    const inputNombre = $('nombre_cliente');
    const inputHora = $('hora_entrega');
    const inputDetalles = $('observaciones_orden');
    const btnProcesar = $('btn-procesar');
    const errorGeneral = $('error-general-orden');
    const resumenModal = $('modal-datos-resumen');

    const esc = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const totalCarrito = () => carrito.reduce((acc, it) => acc + it.precioUnitario, 0);

    // ------------------------------------------------------------------
    // 1. FILTRADO POR CATEGORÍAS
    // ------------------------------------------------------------------
    const btnCategorias = document.querySelectorAll('.btn-categoria');
    const tarjetasProductos = document.querySelectorAll('.tarjeta-producto');

    btnCategorias.forEach(btn => {
        btn.addEventListener('click', () => {
            btnCategorias.forEach(b => {
                b.classList.remove('bg-[#1B4332]', 'text-white');
                b.classList.add('bg-white', 'text-gray-700');
            });
            btn.classList.remove('bg-white', 'text-gray-700');
            btn.classList.add('bg-[#1B4332]', 'text-white');

            const idCategoria = btn.dataset.id;
            tarjetasProductos.forEach(tarjeta => {
                tarjeta.style.display = (idCategoria === '0' || tarjeta.dataset.categoria === idCategoria) ? 'flex' : 'none';
            });
        });
    });

    // ------------------------------------------------------------------
    // 2. AGREGAR PRODUCTOS AL CARRITO
    // ------------------------------------------------------------------
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tarjeta = e.target.closest('.tarjeta-producto');
            let precioTotalItem = parseFloat(btn.dataset.precio);
            const extras = [];
            const checkboxes = tarjeta.querySelectorAll('.extra-checkbox:checked');

            checkboxes.forEach(chk => {
                extras.push({ id: chk.dataset.id, nombre: chk.dataset.nombre, precio: parseFloat(chk.dataset.precio) });
                precioTotalItem += parseFloat(chk.dataset.precio);
            });

            carrito.push({
                idProducto: btn.dataset.id,
                nombre: btn.dataset.nombre,
                precioUnitario: precioTotalItem,
                extras: extras
            });

            actualizarTicket();
            checkboxes.forEach(chk => chk.checked = false);
        });
    });

    // ------------------------------------------------------------------
    // 3. TICKET (carrito)
    // ------------------------------------------------------------------
    function actualizarTicket() {
        let html = '';
        carrito.forEach((item, index) => {
            const extrasHtml = item.extras.length > 0
                ? `<p class="text-[11px] text-amber-800 font-semibold mt-0.5">+ ${esc(item.extras.map(e => e.nombre).join(', '))}</p>`
                : '';
            html += `
                <div class="border-b border-gray-100 pb-2 relative">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-2">
                            <p class="font-bold text-xs text-gray-900">1x ${esc(item.nombre)}</p>
                            ${extrasHtml}
                        </div>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="font-bold text-xs text-gray-900">$${item.precioUnitario.toFixed(2)}</span>
                            <button type="button" onclick="eliminarDelCarrito(${index})" class="text-[10px] text-red-600 hover:text-red-800 mt-0.5 font-bold cursor-pointer">Eliminar</button>
                        </div>
                    </div>
                </div>`;
        });
        listaTicket.innerHTML = html;

        const total = totalCarrito();
        ticketSubtotal.textContent = `$${total.toFixed(2)}`;
        ticketTotal.textContent = `$${total.toFixed(2)}`;

        // "Siguiente" solo con productos en el carrito y con la caja abierta
        btnSiguiente.disabled = carrito.length === 0 || window.cajaAbierta === false;
    }

    window.eliminarDelCarrito = (index) => {
        carrito.splice(index, 1);
        actualizarTicket();
    };

    // ------------------------------------------------------------------
    // 4. ESTADO DE PAGO (Pagado / Por Cancelar)
    // ------------------------------------------------------------------
    const BASE_PAGO = 'h-8 flex items-center justify-center rounded-lg text-[12px] text-center cursor-pointer transition-colors ';
    const PAGO_ACTIVO_VERDE = 'bg-emerald-100 border-2 border-emerald-600 font-bold text-emerald-900';
    const PAGO_ACTIVO_AMBAR = 'bg-amber-100 border-2 border-amber-500 font-bold text-amber-900';
    const PAGO_INACTIVO = 'border border-gray-300 font-semibold text-gray-600 hover:bg-gray-50';

    function setEstadoPago(estado) {
        inputEstado.value = estado;
        const pagado = estado === 'pagado';
        btnPagado.className = BASE_PAGO + (pagado ? PAGO_ACTIVO_VERDE : PAGO_INACTIVO);
        btnPendiente.className = BASE_PAGO + (pagado ? PAGO_INACTIVO : PAGO_ACTIVO_AMBAR);
        // La forma de pago solo aplica si ya se cobró
        contenedorMetodo.classList.toggle('hidden', !pagado);
    }
    btnPagado.addEventListener('click', () => setEstadoPago('pagado'));
    btnPendiente.addEventListener('click', () => setEstadoPago('pendiente'));
    setEstadoPago('pagado');

    // ------------------------------------------------------------------
    // 5. MODAL DE DATOS DEL PEDIDO
    // ------------------------------------------------------------------
    // El input de hora puede ser el original o el "alt" de flatpickr: se resalta el visible.
    const campoVisible = (input) => (input && input._flatpickr && input._flatpickr.altInput) ? input._flatpickr.altInput : input;

    function limpiarErrores() {
        errorGeneral.classList.add('hidden');
        errorGeneral.textContent = '';
        formDatos.querySelectorAll('.error-campo').forEach(p => { p.classList.add('hidden'); p.textContent = ''; });
        [inputNombre, inputHora].forEach(inp => {
            campoVisible(inp).classList.remove('border-red-500', 'ring-2', 'ring-red-500');
        });
    }

    function marcarError(input, mensaje) {
        campoVisible(input).classList.add('border-red-500', 'ring-2', 'ring-red-500');
        const p = formDatos.querySelector(`.error-campo[data-para="${input.id}"]`);
        if (p) { p.textContent = mensaje; p.classList.remove('hidden'); }
    }

    function abrirModalDatos() {
        if (window.cajaAbierta === false) {
            alert('⚠️ Debes abrir caja antes de registrar una venta.');
            return;
        }
        if (carrito.length === 0) return;

        limpiarErrores();
        const n = carrito.length;
        resumenModal.textContent = `${n} producto${n > 1 ? 's' : ''} · Total $${totalCarrito().toFixed(2)} · ` +
            (inputEstado.value === 'pagado' ? 'Pagado' : 'Por cancelar');
        modalDatos.classList.remove('hidden');
        modalDatos.classList.add('flex');
        setTimeout(() => inputNombre.focus(), 50);
    }

    function cerrarModalDatos() {
        modalDatos.classList.add('hidden');
        modalDatos.classList.remove('flex');
    }

    btnSiguiente.addEventListener('click', abrirModalDatos);
    $('btn-volver-modal-datos').addEventListener('click', cerrarModalDatos);
    $('btn-cerrar-modal-datos').addEventListener('click', cerrarModalDatos);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalDatos.classList.contains('hidden') && !enviando) cerrarModalDatos();
    });

    // Acepta "14:30" (24h) o "02:30 PM" (12h). Devuelve "HH:MM" o null.
    function normalizarHora(valor) {
        const m = String(valor).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp])?\.?[Mm]?\.?$/);
        if (!m) return null;
        let h = parseInt(m[1], 10);
        const min = parseInt(m[2], 10);
        if (min > 59) return null;
        if (m[3]) {
            if (h < 1 || h > 12) return null;
            const pm = m[3].toLowerCase() === 'p';
            if (h === 12) h = pm ? 12 : 0;
            else if (pm) h += 12;
        }
        if (h > 23) return null;
        return String(h).padStart(2, '0') + ':' + String(min).padStart(2, '0');
    }

    // Devuelve { ok, cliente, hora, detalles } o { ok:false } mostrando los errores en el modal
    function validarDatos() {
        limpiarErrores();
        let ok = true;
        let primerError = null;

        const cliente = inputNombre.value.trim();
        if (cliente.length < 2) {
            marcarError(inputNombre, 'El nombre del cliente es obligatorio.');
            primerError = primerError || inputNombre;
            ok = false;
        }

        const horaCruda = inputHora.value.trim();
        const hora = normalizarHora(horaCruda);
        if (!horaCruda) {
            marcarError(inputHora, 'La hora solicitada es obligatoria.');
            primerError = primerError || campoVisible(inputHora);
            ok = false;
        } else if (!hora) {
            marcarError(inputHora, 'Hora no válida. Usa un formato como 02:30 PM.');
            primerError = primerError || campoVisible(inputHora);
            ok = false;
        } else {
            const [hh, mm] = hora.split(':').map(Number);
            const minutos = hh * 60 + mm;
            if (minutos < HORA_MIN || minutos > HORA_MAX) {
                marcarError(inputHora, 'Solo se programa entre las 07:00 AM y las 08:30 PM.');
                primerError = primerError || campoVisible(inputHora);
                ok = false;
            }
        }

        if (primerError) primerError.focus();
        return ok ? { ok: true, cliente, hora, detalles: inputDetalles.value.trim() } : { ok: false };
    }

    // ------------------------------------------------------------------
    // 6. PROCESAR LA ORDEN
    // ------------------------------------------------------------------
    function toast(mensaje) {
        const t = document.createElement('div');
        t.className = 'fixed top-20 left-1/2 -translate-x-1/2 z-[60] bg-[#1B4332] text-white border border-[#D4AF37] font-bold text-sm px-5 py-3 rounded-xl shadow-2xl';
        t.textContent = mensaje;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    async function procesarOrden() {
        if (enviando) return;                       // evita doble envío
        if (carrito.length === 0) { cerrarModalDatos(); return; }

        const datos = validarDatos();
        if (!datos.ok) return;                      // se queda en el modal, sin bloquear nada

        enviando = true;
        btnProcesar.disabled = true;
        btnProcesar.textContent = 'Procesando...';

        try {
            const res = await fetch(URL_POS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cliente: datos.cliente,
                    horaEntrega: datos.hora,
                    observaciones: datos.detalles,
                    estadoPago: inputEstado.value,
                    idMetodoPago: selectMetodoPago ? selectMetodoPago.value : 1,
                    items: carrito
                })
            });

            // Se lee como texto primero: si PHP mete un warning/HTML, no se pierde el error real
            const texto = await res.text();
            let data;
            try {
                data = JSON.parse(texto);
            } catch (e) {
                console.error('Respuesta no válida del servidor:', texto);
                throw new Error('El servidor devolvió una respuesta inesperada. Revisa la consola (F12) y el log de PHP.');
            }

            if (!data.success) {
                throw new Error(data.error || data.message || 'No se pudo registrar la orden.');
            }

            // Éxito: limpiar todo y dejar el POS listo para la siguiente orden
            const cliente = datos.cliente;
            carrito = [];
            inputNombre.value = '';
            inputDetalles.value = '';
            if (inputHora._flatpickr) inputHora._flatpickr.clear(); else inputHora.value = '';
            setEstadoPago('pagado');
            actualizarTicket();
            cerrarModalDatos();

            // Mostrar el ticket de la orden recién creada (se lee de la base de datos)
            const ticketOk = window.Ticket ? await window.Ticket.abrir(data.id_orden) : false;
            if (!ticketOk) {
                toast(`✔ Orden ${data.codigo || '#' + data.id_orden} de ${cliente} registrada (no se pudo mostrar el ticket)`);
            }

        } catch (err) {
            console.error(err);
            errorGeneral.textContent = '⚠️ ' + (err.message || 'Error de conexión con el servidor.');
            errorGeneral.classList.remove('hidden');
        } finally {
            // SIEMPRE se libera el botón, pase lo que pase
            enviando = false;
            btnProcesar.disabled = false;
            btnProcesar.textContent = 'Procesar Orden';
        }
    }

    formDatos.addEventListener('submit', (e) => {
        e.preventDefault();
        procesarOrden();
    });

    actualizarTicket();
});
