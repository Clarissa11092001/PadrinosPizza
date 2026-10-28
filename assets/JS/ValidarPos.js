document.addEventListener("DOMContentLoaded", function() {
    // Validar estado de caja al cargar el POS
    fetch('CierreController.php?estado_caja=1')
        .then(res => res.json())
        .then(data => {
            if (!data.activa) {
                // Si la caja NO está abierta, mostramos el modal de apertura de inmediato
                const modalApertura = document.getElementById('modal-apertura');
                if (modalApertura) {
                    modalApertura.classList.remove('hidden');
                    modalApertura.classList.add('flex');
                }
                
                // Deshabilitar la interfaz de venta
                const btnProcesar = document.getElementById('btn-procesar');
                if (btnProcesar) {
                    btnProcesar.disabled = true;
                    btnProcesar.title = "Debes abrir caja antes de realizar una venta";
                }
            }
        });
});