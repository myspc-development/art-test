<?php
namespace ArtPulse\Core;

use Dompdf\Dompdf;

/**
 * Utility class for generating simple PDFs.
 */
class DocumentGenerator
{
    /**
     * Generate a basic ticket PDF and return file path.
     * Returns empty string if dompdf is unavailable.
     *
     * @param array $data {
     *     @type string $event_title Event title.
     *     @type string $ticket_code Ticket code.
     * }
     */
    public static function generate_ticket_pdf(array $data): string
    {
        if (!class_exists(Dompdf::class)) {
            return '';
        }
        $html  = '<h1>' . esc_html($data['event_title'] ?? '') . '</h1>';
        $html .= '<p>' . esc_html__('Ticket Code:', 'artpulse') . ' ' . esc_html($data['ticket_code'] ?? '') . '</p>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $upload = wp_upload_dir();
        $path   = trailingslashit($upload['path']) . 'ticket-' . ($data['ticket_code'] ?? wp_generate_password(8, false)) . '.pdf';
        file_put_contents($path, $dompdf->output());
        return $path;
    }
}
