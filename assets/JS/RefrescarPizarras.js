document.addEventListener("DOMContentLoaded", function () {
    setInterval(function () {
        // Si hay un modal abierto (cobro o ticket), no recargar: se perdería lo que el cajero está haciendo
        const ids = ["modal-cobro-entrega", "modal-ticket"];
        const hayModalAbierto = ids.some(function (id) {
            const m = document.getElementById(id);
            return m && !m.classList.contains("hidden");
        });
        if (hayModalAbierto) return;

        window.location.reload();
    }, 10000);
});
