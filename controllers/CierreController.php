<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Importaciones con rutas y mayúsculas exactas
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/CierreModel.php';

$id_usuario = $_SESSION['id_usuario'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $accion = $_POST['accion'] ?? '';

    // 1. Apertura de Caja
    if ($accion === 'abrir') {
        $monto_inicial = floatval($_POST['monto_inicial'] ?? 0);
        $caja = CierreModel::getCajaActiva($id_usuario);
        if ($caja) {
            echo json_encode(['success' => false, 'message' => 'Ya tienes una caja abierta.']);
            exit;
        }
        CierreModel::abrirCaja($id_usuario, $monto_inicial);
        echo json_encode(['success' => true, 'message' => 'Caja abierta con éxito.']);
        exit;
    }

    // 2. Cierre de Caja
    if ($accion === 'cerrar') {
        $caja = CierreModel::getCajaActiva($id_usuario);
        if (!$caja) {
            echo json_encode(['success' => false, 'message' => 'No hay ninguna caja abierta para cerrar.']);
            exit;
        }

        // Validar órdenes pendientes en cocina (Estados 1 y 2)
        $pendientes = CierreModel::contarOrdenesPendientesTurno($id_usuario, $caja['fecha_apertura']);
        if ($pendientes > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "No puedes cerrar caja. Tienes {$pendientes} orden(es) en preparación o pendientes."
            ]);
            exit;
        }

        $monto_declarado = floatval($_POST['monto_declarado'] ?? 0);
        $ventas = CierreModel::getTotalVentasTurno($id_usuario, $caja['fecha_apertura']);
        $monto_esperado = $caja['monto_inicial'] + $ventas['total_ventas'];
        $diferencia = $monto_declarado - $monto_esperado;
        $observaciones = trim($_POST['observaciones'] ?? '');

        if (abs($diferencia) > 0.01 && strlen($observaciones) < 5) {
            echo json_encode([
                'success' => false, 
                'message' => "Existe una diferencia de $" . number_format($diferencia, 2) . ". Debes escribir una justificación en las observaciones."
            ]);
            exit;
        }

        CierreModel::procesarCierre($caja['id_cierre'], $monto_esperado, $monto_declarado, $diferencia, $observaciones);
        echo json_encode(['success' => true, 'message' => 'Cierre de caja registrado exitosamente.']);
        exit;
    }
}

// Consulta de estado vía GET para modales
if (isset($_GET['estado_caja'])) {
    header('Content-Type: application/json');
    $caja = CierreModel::getCajaActiva($id_usuario);
    if ($caja) {
        $ventas = CierreModel::getTotalVentasTurno($id_usuario, $caja['fecha_apertura']);
        $pendientes = CierreModel::contarOrdenesPendientesTurno($id_usuario, $caja['fecha_apertura']);
        echo json_encode([
            'activa' => true,
            'caja' => $caja,
            'ventas' => $ventas['total_ventas'],
            'num_ordenes' => $ventas['total_ordenes'],
            'esperado' => $caja['monto_inicial'] + $ventas['total_ventas'],
            'pendientes' => $pendientes
        ]);
    } else {
        echo json_encode(['activa' => false]);
    }
    exit;
}
?>