document.addEventListener("DOMContentLoaded", function () {
    
    document.querySelectorAll(".btn-marcar-entregada").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            
            const idOrden = this.dataset.id;
            const codigo = this.dataset.codigo;
            const cliente = this.dataset.cliente;
            const total = this.dataset.total;
            const estaPagado = this.dataset.pagado === "1";
            
            let productos = [];
            try {
                productos = typeof this.dataset.productos === "string" 
                    ? JSON.parse(this.dataset.productos) 
                    : this.dataset.productos;
            } catch (err) {
                productos = [];
            }

            if (estaPagado) {
                if (confirm(`¿Estás seguro de entregar la orden #${codigo} a ${cliente}?`)) {
                    procesarEnvioEntrega(idOrden, 1, this);
                }
            } else {
                abrirModalCobro(idOrden, codigo, cliente, total, productos);
            }
        });
    });

    function abrirModalCobro(idOrden, codigo, cliente, total, productos) {
        document.getElementById("modal-orden-codigo").textContent = `#${codigo}`;
        document.getElementById("modal-cliente-nombre").textContent = cliente;
        document.getElementById("modal-total-cobrar").textContent = `$${parseFloat(total).toFixed(2)}`;
        document.getElementById("modal_id_orden_val").value = idOrden;

        const contProductos = document.getElementById("modal-lista-productos");
        contProductos.innerHTML = "";
        
        if (Array.isArray(productos) && productos.length > 0) {
            productos.forEach(p => {
                const linea = document.createElement("p");
                linea.className = "font-bold";
                linea.textContent = `${p.cantidad || 1}x ${p.nombre || p.producto}`;
                contProductos.appendChild(linea);
            });
        } else {
            contProductos.innerHTML = `<p class="text-gray-400 italic">Sin detalle especificado</p>`;
        }

        const btnConf = document.getElementById("btn-confirmar-cobro-entrega");
        if (btnConf) {
            btnConf.disabled = false;
            btnConf.textContent = "✓ Registrar Pago y Entregar Orden";
        }

        const modal = document.getElementById("modal-cobro-entrega");
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    const btnConfirmar = document.getElementById("btn-confirmar-cobro-entrega");
    if (btnConfirmar) {
        btnConfirmar.addEventListener("click", function () {
            const idOrden = document.getElementById("modal_id_orden_val").value;
            const idMetodo = document.getElementById("modal_id_metodo_pago").value;

            btnConfirmar.disabled = true;
            btnConfirmar.textContent = "Procesando Cobro...";

            procesarEnvioEntrega(idOrden, idMetodo, btnConfirmar);
        });
    }
});

window.cerrarModalCobro = function() {
    const modal = document.getElementById("modal-cobro-entrega");
    if (modal) {
        modal.classList.remove("flex");
        modal.classList.add("hidden");
    }
};

function procesarEnvioEntrega(idOrden, idMetodoPago, btnElement) {
    fetch("/Padrino's_Pizza/controllers/EntregaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id_orden: idOrden,
            idMetodoPago: idMetodoPago
        })
    })
    .then(res => res.text())
    .then(texto => {
        let data;
        try { data = JSON.parse(texto); }
        catch (e) { console.error("Respuesta no válida:", texto); data = { success: false, error: "Respuesta inesperada del servidor (revisa la consola)." }; }

        if (data.success) {
            window.location.reload();
        } else {
            alert("⚠ Error: " + (data.error || "No se pudo procesar la entrega."));
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.textContent = "✓ Registrar Pago y Entregar Orden";
            }
        }
    })
    .catch(err => {
        console.error(err);
        alert("Ocurrió un error en la conexión con el servidor.");
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.textContent = "✓ Registrar Pago y Entregar Orden";
        }
    });
}