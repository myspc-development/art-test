<?php
namespace ArtPulse\Reporting;

use Dompdf\Dompdf;

/**
 * Generates simple CSV and PDF snapshots for event metrics.
 */
use ArtPulse\Core\DocumentGenerator;

class SnapshotBuilder {

	/**
	 * Aggregate monthly event metrics for an organization.
	 *
	 * @param int    $org_id Organization post ID.
	 * @param string $period YYYY-MM formatted month.
	 * @return array<string,int|float>
	 */
	public static function build( int $org_id, string $period ): array {
		$start = date( 'Y-m-01 00:00:00', strtotime( $period . '-01' ) );
		$end   = date( 'Y-m-t 23:59:59', strtotime( $period . '-01' ) );

		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_key'    => '_ap_event_organization',
				'meta_value'  => $org_id,
				'date_query'  => array(
					array(
						'after'     => $start,
						'before'    => $end,
						'inclusive' => true,
					),
				),
				'numberposts' => -1,
			)
		);

		$summary = array(
			'Total Events' => count( $events ),
			'RSVPs'        => 0,
			'Check-Ins'    => 0,
			'Revenue'      => 0.0,
		);

		global $wpdb;
		$checkins = $wpdb->prefix . 'ap_event_checkins';
		$tickets  = $wpdb->prefix . 'ap_tickets';
		$tiers    = $wpdb->prefix . 'ap_event_tickets';

		foreach ( $events as $event ) {
			$rsvps = get_post_meta( $event->ID, 'event_rsvp_list', true );
			if ( is_array( $rsvps ) ) {
				$summary['RSVPs'] += count( $rsvps );
			}

			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $checkins ) ) === $checkins ) {
				$count                 = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT SUM(group_size) FROM $checkins WHERE event_id = %d AND visit_date >= %s AND visit_date <= %s",
						$event->ID,
						$start,
						$end
					)
				);
				$summary['Check-Ins'] += $count;
			}

			if (
				$wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tickets ) ) === $tickets &&
				$wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tiers ) ) === $tiers
			) {
				$rev                 = (float) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT SUM(et.price) FROM $tickets t JOIN $tiers et ON t.ticket_tier_id = et.id WHERE t.event_id = %d AND t.status = 'active' AND t.purchase_date >= %s AND t.purchase_date <= %s",
						$event->ID,
						$start,
						$end
					)
				);
				$summary['Revenue'] += $rev;
			}
		}

		$summary['Revenue'] = round( $summary['Revenue'], 2 );

		return $summary;
	}
	/**
	 * Create a PDF snapshot table of metrics.
	 *
	 * @param array $args {
	 *     @type string $title  Report title.
	 *     @type array  $data   Key/value metric pairs.
	 * }
	 * @return string Path to generated PDF or empty string if dompdf is missing.
	 */
	public static function generate_pdf( array $args ): string {
		if ( ! class_exists( Dompdf::class ) ) {
			return '';
		}

		$title = $args['title'] ?? '';
		$data  = $args['data'] ?? array();

		$html  = '<h1>' . esc_html( $title ) . '</h1>';
		$html .= '<table border="1" cellspacing="0" cellpadding="4"><tbody>';
		foreach ( $data as $label => $value ) {
			$html .= '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( $value ) . '</td></tr>';
		}
		$html  .= '</tbody></table>';
		$upload = wp_upload_dir();
		$slug   = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ?: wp_generate_password( 8, false ) ) );

		if ( class_exists( DocumentGenerator::class ) && method_exists( DocumentGenerator::class, 'generate_pdf_from_html' ) ) {
			return DocumentGenerator::generate_pdf_from_html( $html, 'snapshot-' . $slug );
		}

		$dompdf = new Dompdf();
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4' );
		$dompdf->render();

		$path = trailingslashit( $upload['path'] ) . 'snapshot-' . $slug . '.pdf';
		file_put_contents( $path, $dompdf->output() );

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
	public static function generate_csv( array $args ): string {
		$title = $args['title'] ?? '';
		$data  = $args['data'] ?? array();

		$upload = wp_upload_dir();
		$slug   = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ?: wp_generate_password( 8, false ) ) );
		$path   = trailingslashit( $upload['path'] ) . 'snapshot-' . $slug . '.csv';

		$fp = fopen( $path, 'w' );
		foreach ( $data as $label => $value ) {
			fputcsv( $fp, array( $label, $value ) );
		}
		fclose( $fp );

		return $path;
	}
}
