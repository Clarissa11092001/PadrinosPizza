<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/CatalogoModel.php';
require_once __DIR__ . '/../models/OrdenModel.php';

// Si es una solicitud POST (procesar orden en la caja)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'La orden no contiene productos.']);
        exit;
    }

    $id_usuario = $_SESSION['id_usuario'] ?? 1;
    $estadoPago = $data['estadoPago'] ?? 'pagado';
    $items = $data['items'];

    $resultado = OrdenModel::crearOrden($id_usuario, $estadoPago, $items);
    echo json_encode($resultado);
    exit;
}

// Carga normal GET de la pantalla del POS
$categorias = CatalogoModel::getCategoriasActivas();
$productos = CatalogoModel::getProductosActivos();
$extrasPorProducto = CatalogoModel::getExtrasPorProducto();

require_once __DIR__ . '/../views/cajero/pos.php';
?>