document.addEventListener("DOMContentLoaded", function() {
    const inputHora = document.getElementById("hora_entrega");

    if (inputHora && typeof flatpickr !== "undefined") {
        flatpickr(inputHora, {
            enableTime: true,
            noCalendar: true,
            // Lo que se ENVÍA al servidor: 24h (ej. 14:30). MySQL TIME no entiende "02:30 PM".
            dateFormat: "H:i",
            // Lo que el cajero VE: 12h (ej. 02:30 PM)
            altInput: true,
            altFormat: "h:i K",
            time_24hr: false,
            minTime: "07:00",
            maxTime: "20:30",
            defaultHour: 12,
            minuteIncrement: 5,
            disableMobile: true
        });
    }
    // Si flatpickr no carga (sin internet), el campo sigue funcionando como texto
    // y Pos.js acepta tanto "14:30" como "02:30 PM".
});
