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

// Acción de Anular Orden (nunca se borra: queda registrado quién, cuándo y por qué)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anular_id'])) {
    $resultado = GerenciaModel::anularOrden(
        intval($_POST['anular_id']),
        $_POST['motivo_anulacion'] ?? '',
        $_SESSION['id_usuario'],
        isset($_POST['reembolso_confirmado'])
    );
    // Mensaje que se muestra en el dashboard tras recargar
    $_SESSION['flash_gerencia'] = [
        'tipo'    => $resultado['success'] ? 'ok' : 'error',
        'mensaje' => $resultado['message']
    ];
    header("Location: GerenciaController.php");
    exit();
}

// Obtener métricas y listas para el Dashboard
$kpis = GerenciaModel::getKpisDelDia();
$auditoria = GerenciaModel::getAuditoriaOrdenes();
$ventasCategoria = GerenciaModel::getVentasPorCategoria();

// Mensaje (éxito/error) de la última acción, se muestra una sola vez
$flash = $_SESSION['flash_gerencia'] ?? null;
unset($_SESSION['flash_gerencia']);

// Cargar la vista de Gerencia
require_once __DIR__ . '/../views/gerencia/dashboard.php';
?>