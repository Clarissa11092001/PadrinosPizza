<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/GerenciaModel.php';

// Verificar que solo los Administradores ingresen a Gerencia
if ($_SESSION['id_rol'] != 1) {
    header("Location: PosController.php");
    exit();
}

// Acción de Anular Orden (Soft Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anular_id'])) {
    $id_orden = intval($_POST['anular_id']);
    $motivo = trim($_POST['motivo_anulacion'] ?? 'Anulada por Gerencia');
    GerenciaModel::anularOrden($id_orden, $motivo);
    header("Location: GerenciaController.php");
    exit();
}

// Obtener métricas y listas para el Dashboard
$kpis = GerenciaModel::getKpisDelDia();
$auditoria = GerenciaModel::getAuditoriaOrdenes();
$ventasCategoria = GerenciaModel::getVentasPorCategoria();

// Cargar la vista de Gerencia
require_once __DIR__ . '/../views/gerencia/dashboard.php';
?>