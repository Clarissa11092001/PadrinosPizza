<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/OrdenModel.php';

// Acción de cambiar estado a Entregada (Estado 4)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden'])) {
    $id_orden = intval($_POST['id_orden']);
    OrdenModel::marcarComoEntregada($id_orden);
    header("Location: EntregaController.php");
    exit();
}

// Cargar órdenes en seguimiento
$ordenes = OrdenModel::getOrdenesParaEntrega();

require_once __DIR__ . '/../views/cajero/Pizzarra.php';
?>