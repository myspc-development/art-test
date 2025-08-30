<?php
namespace ArtPulse\Reporting;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Support\FileSystem;

/**
 * Exports budget line items for an event as CSV or PDF.
 */
class BudgetExportController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/budget/export' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/budget/export',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'export' ),
					'permission_callback' => array( self::class, 'can_export' ),
					'args'                => array(
						'event_id'  => array(
							'required'          => false,
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
						),
						'event_ids' => array(
							'required'          => false,
							'validate_callback' => 'is_string',
						),
						'format'    => array(
							'default'           => 'pdf',
							'validate_callback' => fn( $f ) => in_array( $f, array( 'pdf', 'csv' ), true ),
						),
					),
				)
			);
		}
	}

	public static function can_export(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function export( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id  = absint( $request->get_param( 'event_id' ) );
		$ids_param = $request->get_param( 'event_ids' );
		$format    = $request->get_param( 'format' ) ?: 'pdf';

		$ids = array();
		if ( $ids_param ) {
			foreach ( explode( ',', (string) $ids_param ) as $id ) {
				$id = absint( $id );
				if ( $id ) {
					$ids[] = $id;
				}
			}
		}
		if ( $event_id ) {
			$ids[] = $event_id;
		}
		$ids = array_unique( $ids );

		if ( ! $ids ) {
			return new WP_Error( 'invalid_event', 'Invalid event ID', array( 'status' => 400 ) );
		}

		$lines = array();
		foreach ( $ids as $id ) {
			$lines = array_merge( $lines, (array) get_post_meta( $id, 'ap_budget_lines', true ) );
		}
		$totals = array(
			'Estimated Total' => 0,
			'Actual Total'    => 0,
		);
		foreach ( $lines as $line ) {
			$totals['Estimated Total'] += (float) ( $line['estimated'] ?? 0 );
			$totals['Actual Total']    += (float) ( $line['actual'] ?? 0 );
		}

		$title_id = $ids[0];
		if ( $format === 'csv' ) {
			$path = SnapshotBuilder::generate_csv(
				array(
					'title' => 'Budget ' . $title_id,
					'data'  => $totals,
				)
			);
			$data = file_get_contents( $path );
			FileSystem::safe_unlink( $path );
			return new WP_REST_Response(
				$data,
				200,
				array(
					'Content-Type'        => 'text/csv',
					'Content-Disposition' => 'attachment; filename="budget-' . $title_id . '.csv"',
				)
			);
		}

		$path = SnapshotBuilder::generate_pdf(
			array(
				'title' => 'Budget ' . $title_id,
				'data'  => $totals,
			)
		);
		$data = file_get_contents( $path );
		FileSystem::safe_unlink( $path );
		return new WP_REST_Response(
			$data,
			200,
			array(
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="budget-' . $title_id . '.pdf"',
			)
		);
	}
}
