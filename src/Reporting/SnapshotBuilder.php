<?php
namespace ArtPulse\Reporting;

use Dompdf\Dompdf;

/**
 * Generates simple CSV and PDF snapshots for event metrics.
 */
class SnapshotBuilder
{
    /**
     * Create a PDF snapshot table of metrics.
     *
     * @param array $args {
     *     @type string $title  Report title.
     *     @type array  $data   Key/value metric pairs.
     * }
     * @return string Path to generated PDF or empty string if dompdf is missing.
     */
    public static function generate_pdf(array $args): string
    {
        if (!class_exists(Dompdf::class)) {
            return '';
        }

        $title = $args['title'] ?? '';
        $data  = $args['data'] ?? [];

        $html  = '<h1>' . esc_html($title) . '</h1>';
        $html .= '<table border="1" cellspacing="0" cellpadding="4"><tbody>';
        foreach ($data as $label => $value) {
            $html .= '<tr><td>' . esc_html($label) . '</td><td>' . esc_html($value) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $upload = wp_upload_dir();
        $slug   = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title ?: wp_generate_password(8, false)));
        $path   = trailingslashit($upload['path']) . 'snapshot-' . $slug . '.pdf';
        file_put_contents($path, $dompdf->output());

        return $path;
    }

    /**
     * Create a CSV snapshot file of metrics.
     *
     * @param array $args {
     *     @type string $title Report title.
     *     @type array  $data  Key/value metric pairs.
     * }
     * @return string Path to generated CSV.
     */
    public static function generate_csv(array $args): string
    {
        $title = $args['title'] ?? '';
        $data  = $args['data'] ?? [];

        $upload = wp_upload_dir();
        $slug   = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title ?: wp_generate_password(8, false)));
        $path   = trailingslashit($upload['path']) . 'snapshot-' . $slug . '.csv';

        $fp = fopen($path, 'w');
        foreach ($data as $label => $value) {
            fputcsv($fp, [$label, $value]);
        }
        fclose($fp);

        return $path;
    }
}
