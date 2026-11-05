<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/CatalogoModel.php';
require_once __DIR__ . '/../models/OrdenModel.php';
require_once __DIR__ . '/../models/CierreModel.php';

/**
 * Acepta '14:30', '14:30:00', '2:30 PM' o '02:30 pm' y devuelve 'HH:MM:SS' (24h),
 * que es lo que entiende la columna TIME de MySQL. Devuelve null si no es una hora válida.
 */
function normalizarHora(string $valor): ?string {
    if (!preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp])?\.?[Mm]?\.?$/', trim($valor), $m)) {
        return null;
    }
    $h = intval($m[1]);
    $min = intval($m[2]);
    if ($min > 59) return null;
    if (!empty($m[3])) { // formato 12 horas
        if ($h < 1 || $h > 12) return null;
        $esPm = strtolower($m[3]) === 'p';
        if ($h === 12) { $h = $esPm ? 12 : 0; } elseif ($esPm) { $h += 12; }
    }
    if ($h > 23) return null;
    return sprintf('%02d:%02d:00', $h, $min);
}

// Procesar la orden vía POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['items'])) {
        echo json_encode(['success' => false, 'error' => 'La orden no contiene productos.']);
        exit;
    }

    $id_usuario = $_SESSION['id_usuario'] ?? 1;

    // No se puede vender sin caja abierta (el front lo bloquea, aquí se garantiza)
    if (!CierreModel::getCajaActiva($id_usuario)) {
        echo json_encode(['success' => false, 'error' => 'Debes abrir caja antes de registrar una venta.']);
        exit;
    }

    // VALIDACIÓN EN BACKEND: nombre obligatorio
    $cliente = trim($data['cliente'] ?? '');
    if (mb_strlen($cliente) < 2) {
        echo json_encode(['success' => false, 'error' => 'El nombre del cliente es obligatorio.']);
        exit;
    }

    // VALIDACIÓN EN BACKEND: hora obligatoria, válida y dentro del horario (07:00 - 20:30)
    $horaEntrega = normalizarHora((string)($data['horaEntrega'] ?? ''));
    if ($horaEntrega === null) {
        echo json_encode(['success' => false, 'error' => 'La hora solicitada es obligatoria y debe tener un formato válido.']);
        exit;
    }
    if ($horaEntrega < '07:00:00' || $horaEntrega > '20:30:00') {
        echo json_encode(['success' => false, 'error' => 'Las órdenes solo se programan entre las 07:00 AM y las 08:30 PM.']);
        exit;
    }

    // Detalles/observaciones: opcional
    $observaciones = trim($data['observaciones'] ?? '');
    $estadoPago    = ($data['estadoPago'] ?? 'pagado') === 'pendiente' ? 'pendiente' : 'pagado';
    $idMetodoPago  = intval($data['idMetodoPago'] ?? 1);
    if ($idMetodoPago < 1 || $idMetodoPago > 3) $idMetodoPago = 1;
    $items         = is_array($data['items'] ?? null) ? $data['items'] : [];

    $resultado = OrdenModel::crearOrden($id_usuario, $cliente, $horaEntrega, $observaciones, $estadoPago, $idMetodoPago, $items);
    echo json_encode($resultado);
    exit;
}

// Carga normal GET de la pantalla del POS
$categorias = CatalogoModel::getCategoriasActivas();
// Solo se venden productos activos (el catálogo de gerencia sí necesita ver también los inactivos)
$productos = array_values(array_filter(CatalogoModel::getProductosActivos(), fn($p) => intval($p['activo']) === 1));
$extrasPorProducto = CatalogoModel::getExtrasPorProducto();

require_once __DIR__ . '/../views/cajero/Pos.php';
?>