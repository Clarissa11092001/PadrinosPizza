document.addEventListener("DOMContentLoaded", function() {
    // Validar estado de caja al cargar el POS
    fetch('CierreController.php?estado_caja=1')
        .then(res => res.json())
        .then(data => {
            if (!data.activa) {
                // Pos.js lee esta bandera para no habilitar "Siguiente" sin caja abierta
                window.cajaAbierta = false;

                const modalApertura = document.getElementById('modal-apertura');
                if (modalApertura) {
                    modalApertura.classList.remove('hidden');
                    modalApertura.classList.add('flex');
                }

                const btnSiguiente = document.getElementById('btn-siguiente');
                if (btnSiguiente) {
                    btnSiguiente.disabled = true;
                    btnSiguiente.title = "Debes abrir caja antes de realizar una venta";
                }
            }
        })
        .catch(err => console.error('No se pudo verificar el estado de la caja:', err));
});
