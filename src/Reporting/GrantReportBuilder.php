<?php
namespace ArtPulse\Reporting;

use Dompdf\Dompdf;

/**
 * Build grant reports in PDF format for organization subscriptions.
 */
class GrantReportBuilder {

	/**
	 * Generate a grant report PDF.
	 *
	 * @param array $args {
	 *     @type string $title   Report title.
	 *     @type array  $summary Key/value summary metrics.
	 *     @type array  $events  Top events list.
	 *     @type array  $donors  Donor summary rows.
	 * }
	 * @return string Path to generated PDF or empty string when Dompdf is missing.
	 */
	public static function generate_pdf( array $args ): string {
		if ( ! class_exists( Dompdf::class ) ) {
			return '';
		}

		$title   = $args['title'] ?? 'Grant Report';
		$summary = $args['summary'] ?? array();
		$events  = $args['events'] ?? array();
		$donors  = $args['donors'] ?? array();

		$html = '<h1>' . esc_html( $title ) . '</h1>';
		if ( $summary ) {
			$html .= '<h2>Summary</h2><table border="1" cellspacing="0" cellpadding="4"><tbody>';
			foreach ( $summary as $label => $value ) {
				$html .= '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( $value ) . '</td></tr>';
			}
			$html .= '</tbody></table>';
		}
		if ( $events ) {
			$html .= '<h2>Top Events</h2><table border="1" cellspacing="0" cellpadding="4"><thead><tr><th>Event</th><th>RSVPs</th></tr></thead><tbody>';
			foreach ( $events as $row ) {
				$html .= '<tr><td>' . esc_html( $row['title'] ?? '' ) . '</td><td>' . esc_html( $row['rsvps'] ?? '' ) . '</td></tr>';
			}
			$html .= '</tbody></table>';
		}
		if ( $donors ) {
			$html .= '<h2>Donors</h2><table border="1" cellspacing="0" cellpadding="4"><thead><tr><th>Name</th><th>Amount</th></tr></thead><tbody>';
			foreach ( $donors as $row ) {
				$html .= '<tr><td>' . esc_html( $row['name'] ?? '' ) . '</td><td>' . esc_html( $row['amount'] ?? '' ) . '</td></tr>';
			}
			$html .= '</tbody></table>';
		}

		$dompdf = new Dompdf();
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4' );
		$dompdf->render();

		$upload = wp_upload_dir();
		$slug   = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ) );
		$path   = trailingslashit( $upload['path'] ) . 'grant-' . $slug . '.pdf';
		file_put_contents( $path, $dompdf->output() );

		return $path;
	}
}
