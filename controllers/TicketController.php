<?php
// Devuelve en JSON los datos de un ticket: controllers/TicketController.php?id=ID_ORDEN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/OrdenModel.php';

if (ob_get_length()) { ob_clean(); }
header('Content-Type: application/json; charset=utf-8');

// Solo gerencia (1) y cajero (2) pueden ver tickets
$idRol = intval($_SESSION['id_rol'] ?? 0);
if (!in_array($idRol, [1, 2], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para ver tickets.']);
    exit;
}

$id_orden = intval($_GET['id'] ?? 0);
if ($id_orden <= 0) {
    echo json_encode(['success' => false, 'error' => 'Orden no especificada.']);
    exit;
}

try {
    $ticket = OrdenModel::getTicket($id_orden);
    if (!$ticket) {
        echo json_encode(['success' => false, 'error' => 'La orden no existe.']);
        exit;
    }
    echo json_encode(['success' => true, 'ticket' => $ticket], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'No se pudo generar el ticket: ' . $e->getMessage()]);
}
exit;
