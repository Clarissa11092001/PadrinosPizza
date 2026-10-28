<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/CocinaModel.php';

// Cambiar estado si se recibe la petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden'], $_POST['nuevo_estado'])) {
    CocinaModel::actualizarEstado($_POST['id_orden'], $_POST['nuevo_estado']);
    // Recargar la página para evitar reenvío de formulario
    header("Location: CocinaController.php");
    exit;
}

// Obtener órdenes agrupadas
$ordenes = CocinaModel::getOrdenesDelDia();

// Calcular total de órdenes activas (excluyendo Entregadas)
$totalActivas = count($ordenes['Pendiente']) + count($ordenes['En preparación']) + count($ordenes['Lista']);

require_once __DIR__ . '/../views/cocina/pizarra.php';
?>