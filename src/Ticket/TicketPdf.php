<?php
namespace App\Ticket;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Genera el ticket de una orden en PDF (ancho de papel térmico: 80 mm) con mPDF.
 *
 * Recibe el arreglo que arma OrdenModel::getTicket() (datos leídos de la base de datos)
 * y devuelve el contenido binario del PDF.
 */
class TicketPdf
{
    private const ANCHO_MM = 80;

    public static function generar(array $ticket): string
    {
        $html = self::construirHtml($ticket);

        // Primera pasada en una página muy alta solo para MEDIR cuánto ocupa el contenido,
        // así el PDF final sale con el largo exacto del ticket (sin hojas casi vacías).
        $alto = null;
        $medidor = self::nuevoMpdf(1000);
        $medidor->WriteHTML($html);
        $y = $medidor->y ?? null;
        if (is_numeric($y) && $y > 0) {
            $alto = (int) ceil($y + 8);
        }
        if ($alto === null) {
            $alto = self::estimarAlto($ticket); // plan B si no se pudo medir
        }

        $mpdf = self::nuevoMpdf(max(60, $alto));
        $mpdf->SetTitle('Ticket ' . $ticket['codigo']);
        $mpdf->SetAuthor("Padrino's Pizza");
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    private static function nuevoMpdf(int $altoMm): Mpdf
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf_padrinos';
        if (!is_dir($tmp)) {
            @mkdir($tmp, 0777, true);
        }

        return new Mpdf([
            'mode'              => 'utf-8',
            'format'            => [self::ANCHO_MM, $altoMm],
            'margin_left'       => 4,
            'margin_right'      => 4,
            'margin_top'        => 5,
            'margin_bottom'     => 5,
            'default_font'      => 'dejavusansmono',
            'default_font_size' => 8,
            'tempDir'           => $tmp,
        ]);
    }

    private static function estimarAlto(array $t): int
    {
        $alto = 110;
        foreach ($t['items'] as $it) {
            $alto += 5 * max(1, (int) ceil((mb_strlen($it['nombre']) + 4) / 26));
            $alto += 4 * count($it['extras']);
        }
        $alto += 4 * (int) ceil(mb_strlen($t['observaciones'] ?? '') / 30);
        return $alto;
    }

    /** HTML compatible con mPDF (usa tablas, no flexbox). */
    private static function construirHtml(array $t): string
    {
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m = fn($n) => '$' . number_format((float) $n, 2);
        $linea = '<div style="border-top: 1px dashed #000; margin: 2mm 0;"></div>';
        $fila = fn($izq, $der, $estilo = '') =>
            '<table width="100%" cellpadding="0" cellspacing="0" style="' . $estilo . '"><tr>'
            . '<td>' . $izq . '</td><td align="right">' . $der . '</td></tr></table>';

        $h  = '<div style="text-align:center; font-size:11pt; font-weight:bold;">PADRINO\'S PIZZA</div>';
        $h .= '<div style="text-align:center;">Ticket de orden</div>' . $linea;
        $h .= '<div style="text-align:center; font-size:11pt; font-weight:bold;">' . $e($t['codigo']) . '</div>';
        $h .= '<div style="text-align:center;">' . $e($t['fecha']) . '</div>' . $linea;

        $h .= $fila('Cliente:', $e($t['cliente']));
        if (!empty($t['hora_entrega'])) {
            $h .= $fila('Hora solicitada:', $e($t['hora_entrega']));
        }
        if (!empty($t['cajero'])) {
            $h .= $fila('Atendió:', $e($t['cajero']));
        }
        $h .= $linea;

        foreach ($t['items'] as $it) {
            $h .= $fila($it['cantidad'] . 'x ' . $e($it['nombre']), $m($it['precio_base'] * $it['cantidad']));
            foreach ($it['extras'] as $ex) {
                $h .= $fila('&nbsp;&nbsp;+ ' . $e($ex['nombre']), $m($ex['precio']), 'font-size:7pt;');
            }
        }

        $h .= $linea;
        $h .= $fila('Subtotal:', $m($t['subtotal']));
        if ($t['descuento'] > 0) {
            $h .= $fila('Descuento:', '-' . $m($t['descuento']));
        }
        $h .= $fila('TOTAL:', $m($t['total']), 'font-size:11pt; font-weight:bold;');
        $h .= $linea;

        if ($t['pagado']) {
            $h .= '<div style="text-align:center; font-size:10pt; font-weight:bold;">*** PAGADO ***</div>';
            $h .= '<div style="text-align:center;">' . $e($t['metodo_pago']) . ' · ' . $m($t['monto_pagado']) . '</div>';
            if (!empty($t['fecha_pago'])) {
                $h .= '<div style="text-align:center;">' . $e($t['fecha_pago']) . '</div>';
            }
        } else {
            $h .= '<div style="text-align:center; font-size:10pt; font-weight:bold;">*** POR CANCELAR ***</div>';
            $h .= '<div style="text-align:center;">Pago pendiente: cobrar al entregar</div>';
        }

        if (!empty($t['observaciones'])) {
            $h .= $linea . '<div><b>Detalles:</b> ' . $e($t['observaciones']) . '</div>';
        }

        $h .= $linea . '<div style="text-align:center;">¡Gracias por preferirnos!</div>';
        return $h;
    }
}
