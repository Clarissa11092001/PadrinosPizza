<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/OrdenModel.php';

// Capturar peticiones AJAX (POST JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar cualquier salida previa para enviar un JSON puro
    if (ob_get_length()) { ob_clean(); }
    header('Content-Type: application/json');

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $id_orden     = intval($data['id_orden'] ?? $_POST['id_orden'] ?? 0);
    $idMetodoPago = intval($data['idMetodoPago'] ?? 1);
    $id_usuario   = $_SESSION['id_usuario'] ?? 1;

    if ($id_orden > 0) {
        $resultado = OrdenModel::procesarEntregaYCobro($id_orden, $idMetodoPago, $id_usuario);
        echo json_encode($resultado);
        exit();
    }

    echo json_encode(['success' => false, 'error' => 'ID de orden no válido']);
    exit();
}

// Cargar órdenes para la vista normal del Cajero
$ordenes = OrdenModel::getOrdenesParaEntrega();

require_once __DIR__ . '/../views/cajero/Pizzarra.php';
?>