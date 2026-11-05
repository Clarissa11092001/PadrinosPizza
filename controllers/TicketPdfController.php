<?php
// Muestra el ticket de una orden en PDF: controllers/TicketPdfController.php?id=ID_ORDEN
// Requiere haber ejecutado "composer install" (instala mPDF en /vendor).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/OrdenModel.php';

function salirConTexto(int $codigo, string $mensaje): void {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

// Solo gerencia (1) y cajero (2) pueden ver tickets
if (!in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2], true)) {
    salirConTexto(403, 'No tienes permiso para ver tickets.');
}

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    salirConTexto(500, "Faltan las dependencias de Composer.\n\nAbre una terminal en la carpeta del proyecto y ejecuta:\n\n    composer install\n");
}
require_once $autoload;

$id_orden = intval($_GET['id'] ?? 0);
$ticket = $id_orden > 0 ? OrdenModel::getTicket($id_orden) : null;
if (!$ticket) {
    salirConTexto(404, 'La orden no existe.');
}

try {
    $pdf = \App\Ticket\TicketPdf::generar($ticket);
} catch (\Throwable $e) {
    salirConTexto(500, 'No se pudo generar el PDF: ' . $e->getMessage());
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ticket-' . $ticket['codigo'] . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
