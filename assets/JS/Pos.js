document.addEventListener('DOMContentLoaded', () => {
    let carrito = [];
    const listaTicket = document.getElementById('lista-ticket');
    const ticketSubtotal = document.getElementById('ticket-subtotal');
    const ticketTotal = document.getElementById('ticket-total');
    const btnProcesar = document.getElementById('btn-procesar');

    // Campos de cliente y entrega
    const inputNombreCliente = document.getElementById('nombre_cliente');
    const inputHoraEntrega = document.getElementById('hora_entrega');
    const inputObservaciones = document.getElementById('observaciones_orden');

    // 1. FILTRADO POR CATEGORÍAS
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
                if (idCategoria === "0" || tarjeta.dataset.categoria === idCategoria) {
                    tarjeta.style.display = 'flex';
                } else {
                    tarjeta.style.display = 'none';
                }
            });
        });
    });

    // 2. AGREGAR PRODUCTOS AL CARRITO
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tarjeta = e.target.closest('.tarjeta-producto');
            const idProducto = btn.dataset.id;
            const nombreProducto = btn.dataset.nombre;
            const precioBase = parseFloat(btn.dataset.precio);
            
            let extras = [];
            let precioTotalItem = precioBase;
            const checkboxes = tarjeta.querySelectorAll('.extra-checkbox:checked');
            
            checkboxes.forEach(chk => {
                extras.push({
                    id: chk.dataset.id,
                    nombre: chk.dataset.nombre,
                    precio: parseFloat(chk.dataset.precio)
                });
                precioTotalItem += parseFloat(chk.dataset.precio);
            });

            carrito.push({
                idProducto: idProducto,
                nombre: nombreProducto,
                precioUnitario: precioTotalItem,
                extras: extras
            });

            actualizarTicket();
            checkboxes.forEach(chk => chk.checked = false);
        });
    });

    // 3. ACTUALIZAR INTERFAZ DEL TICKET
    function actualizarTicket() {
        listaTicket.innerHTML = '';
        let total = 0;

        carrito.forEach((item, index) => {
            total += item.precioUnitario;
            
            let extrasHtml = item.extras.length > 0 
                ? `<p class="text-[11px] text-amber-800 font-semibold mt-0.5">+ ${item.extras.map(e => e.nombre).join(', ')}</p>` 
                : '';

            listaTicket.innerHTML += `
                <div class="border-b border-gray-100 pb-2 relative">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 pr-2">
                            <p class="font-bold text-xs text-gray-900">1x ${item.nombre}</p>
                            ${extrasHtml}
                        </div>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="font-bold text-xs text-gray-900">$${item.precioUnitario.toFixed(2)}</span>
                            <button onclick="eliminarDelCarrito(${index})" class="text-[10px] text-red-600 hover:text-red-800 mt-0.5 font-bold cursor-pointer">Eliminar</button>
                        </div>
                    </div>
                </div>
            `;
        });

        ticketSubtotal.textContent = `$${total.toFixed(2)}`;
        ticketTotal.textContent = `$${total.toFixed(2)}`;
        
        btnProcesar.disabled = carrito.length === 0;
    }

    window.eliminarDelCarrito = (index) => {
        carrito.splice(index, 1);
        actualizarTicket();
    };

    // 4. SELECCIÓN DE ESTADO DE PAGO (PAGADO / PENDIENTE)
    const btnPagoRealizado = document.getElementById('btn-pago-realizado');
    const btnPagoPendiente = document.getElementById('btn-pago-pendiente');
    const inputEstado = document.getElementById('estado_pago_seleccionado');

    if (btnPagoRealizado && btnPagoPendiente) {
        btnPagoRealizado.addEventListener('click', () => {
            btnPagoRealizado.className = 'estado-pago activo bg-emerald-100 border-2 border-emerald-600 py-2 rounded-xl text-xs font-bold text-emerald-900 text-center cursor-pointer';
            btnPagoPendiente.className = 'estado-pago border border-gray-300 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 text-center cursor-pointer';
            inputEstado.value = 'pagado';
        });

        btnPagoPendiente.addEventListener('click', () => {
            btnPagoPendiente.className = 'estado-pago activo bg-amber-100 border-2 border-amber-600 py-2 rounded-xl text-xs font-bold text-amber-900 text-center cursor-pointer';
            btnPagoRealizado.className = 'estado-pago border border-gray-300 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 text-center cursor-pointer';
            inputEstado.value = 'pendiente';
        });
    }

    // 5. PROCESAR ORDEN E INSERTAR CON VALIDACIONES
    btnProcesar.addEventListener('click', () => {
        if (carrito.length === 0) return;

        const clienteVal = inputNombreCliente ? inputNombreCliente.value.trim() : '';
        const horaVal = inputHoraEntrega ? inputHoraEntrega.value : '';

        // VALIDACIÓN 1: Nombre del Cliente Obligatorio
        if (clienteVal.length < 2) {
            alert("⚠ Por favor ingresa el Nombre del Cliente antes de procesar.");
            if (inputNombreCliente) inputNombreCliente.focus();
            return;
        }

        // VALIDACIÓN 2: Rango de Horarios Permitiendo de 07:00 a 20:30 (8:30 PM)
        if (horaVal) {
            const [hora, minutos] = horaVal.split(':').map(Number);
            const minutosTotales = hora * 60 + minutos;

            const minPermitido = 7 * 60;        // 07:00 AM
            const maxPermitido = 20 * 60 + 30;  // 08:30 PM (20:30)

            if (minutosTotales < minPermitido || minutosTotales > maxPermitido) {
                alert("⏰ Horario no válido: Las órdenes deben ser entregadas entre las 07:00 AM y las 08:30 PM.");
                if (inputHoraEntrega) inputHoraEntrega.focus();
                return;
            }
        }

        btnProcesar.disabled = true;
        btnProcesar.textContent = 'Procesando...';

        const ordenData = {
            cliente: clienteVal,
            estadoPago: inputEstado.value,
            horaEntrega: horaVal,
            observaciones: inputObservaciones ? inputObservaciones.value.trim() : '',
            items: carrito
        };

        fetch('/Padrino\'s_Pizza/controllers/PosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(ordenData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`✔ ¡Orden de ${clienteVal} (#${data.codigo || data.id_orden}) procesada con éxito!`);
                
                // Limpiar campos
                carrito = [];
                if (inputNombreCliente) inputNombreCliente.value = '';
                if (inputHoraEntrega) inputHoraEntrega.value = '';
                if (inputObservaciones) inputObservaciones.value = '';
                
                actualizarTicket();
            } else {
                alert('⚠ Error al registrar la orden: ' + (data.error || data.message));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error en la conexión con el servidor.');
        })
        .finally(() => {
            btnProcesar.textContent = 'Procesar e Imprimir Orden';
        });
    });
});