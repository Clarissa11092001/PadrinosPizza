<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/OrdenModel.php';

// Procesar Petición AJAX / POST de Entrega o Cobro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    echo json_encode(['success' => false, 'message' => 'ID de orden no válido']);
    exit();
}

// Cargar órdenes en seguimiento para la vista del Cajero
$ordenes = OrdenModel::getOrdenesParaEntrega();

require_once __DIR__ . '/../views/cajero/Pizzarra.php';
?>