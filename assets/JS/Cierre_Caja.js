const URL_LOGOUT = "LoginController.php?action=logout";

// Variable global para cálculo de diferencia
let montoEsperadoActual = 0;

function abrirModalCierre() {
  console.log("Verificando órdenes pendientes antes del cierre...");

  // 1. Verificar si hay órdenes sin entregar o sin cobrar
  fetch('CierreController.php?accion=verificar_pendientes')
    .then(res => res.json())
    .then(data => {
      if (data.ordenes_pendientes > 0) {
        alert(`⛔ ATENCIÓN: No puedes realizar el Cierre de Caja aún.\n\nTienes ${data.ordenes_pendientes} orden(es) pendiente(s) de entregar o cobrar en el sistema. Debes procesarlas o marcarlas como mermas/anuladas antes de cerrar el turno.`);
        return;
      }
      
      // 2. Si no hay pendientes, consultar estado de caja
      consultarEstadoCajaYMostrarModal();
    })
    .catch(() => {
      // Si la ruta previa no devuelve JSON, procede a la verificación normal
      consultarEstadoCajaYMostrarModal();
    });
}

function consultarEstadoCajaYMostrarModal() {
  fetch('CierreController.php?estado_caja=1')
    .then(res => res.json())
    .then(data => {
      if (!data.activa) {
        const modalApertura = document.getElementById('modal-apertura');
        if (modalApertura) {
          modalApertura.classList.remove('hidden');
          modalApertura.classList.add('flex');
        } else {
          window.location.href = URL_LOGOUT;
        }
      } else {
        montoEsperadoActual = parseFloat(data.esperado);

        const elBase = document.getElementById('cierre-base');
        const elVentas = document.getElementById('cierre-ventas');
        const elEsperado = document.getElementById('cierre-esperado');

        if (elBase) elBase.textContent = '$' + parseFloat(data.caja.monto_inicial).toFixed(2);
        if (elVentas) elVentas.textContent = '+$' + parseFloat(data.ventas).toFixed(2);
        if (elEsperado) elEsperado.textContent = '$' + montoEsperadoActual.toFixed(2);
        
        const modalCierre = document.getElementById('modal-cierre');
        if (modalCierre) {
          modalCierre.classList.remove('hidden');
          modalCierre.classList.add('flex');
        }
      }
    })
    .catch(err => {
      console.error("Error al consultar la caja:", err);
      window.location.href = URL_LOGOUT;
    });
}

function cerrarModalApertura() {
  const modalApertura = document.getElementById('modal-apertura');
  if (modalApertura) {
    modalApertura.classList.add('hidden');
    modalApertura.classList.remove('flex');
  }
}

function procesarApertura(e) {
  e.preventDefault();
  const inputMonto = document.getElementById('monto_inicial_input');
  if (!inputMonto) return;

  const monto = inputMonto.value;
  const formData = new FormData();
  formData.append('accion', 'abrir');
  formData.append('monto_inicial', monto);

  fetch('CierreController.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        location.reload();
      }
    })
    .catch(err => {
      console.error("Error al abrir caja:", err);
      alert("Error en la petición de apertura.");
    });
}

function cerrarModalCierre() {
  const modalCierre = document.getElementById('modal-cierre');
  if (modalCierre) {
    modalCierre.classList.add('hidden');
    modalCierre.classList.remove('flex');
  }
}

function cerrarSesionSinCierre() {
  if (confirm('¿Seguro que deseas salir al Login sin realizar el cierre de caja? El turno continuará abierto.')) {
    window.location.href = URL_LOGOUT;
  }
}

function procesarCierreYSalir(e) {
  e.preventDefault();

  const inputDeclarado = document.getElementById('monto_declarado_input');
  const inputObs = document.getElementById('observaciones_input');

  if (!inputDeclarado || !inputObs) return;

  const declarado = parseFloat(inputDeclarado.value) || 0;
  const observaciones = inputObs.value.trim();

  const diferencia = declarado - montoEsperadoActual;

  if (Math.abs(diferencia) > 0.01 && observaciones.length < 5) {
    alert(`Existe una diferencia de $${diferencia.toFixed(2)}. Por favor escribe una justificación en las observaciones.`);
    inputObs.focus();
    return;
  }

  const formData = new FormData();
  formData.append('accion', 'cerrar');
  formData.append('monto_declarado', declarado);
  formData.append('observaciones', observaciones);

  fetch('CierreController.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("✔ Cierre de caja registrado exitosamente.");
        window.location.href = URL_LOGOUT;
      } else {
        alert("⚠ Error: " + data.message);
      }
    })
    .catch(err => {
      console.error("Error al procesar cierre:", err);
      alert("Ocurrió un fallo en la red o en el servidor.");
    });
}