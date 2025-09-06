<?php
namespace ArtPulse\Reporting;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Support\FileSystem;

class SnapshotExportController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		$args = array(
			'org_id' => array(
				'type'     => 'integer',
				'required' => true,
			),
			'period' => array(
				'type'     => 'string',
				'required' => true,
			),
		);
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/reporting/snapshot' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/reporting/snapshot',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'summary' ),
					'permission_callback' => array( self::class, 'can_export' ),
					'args'                => $args,
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/reporting/snapshot.csv' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/reporting/snapshot.csv',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'csv' ),
					'permission_callback' => array( self::class, 'can_export' ),
					'args'                => $args,
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/reporting/snapshot.pdf' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/reporting/snapshot.pdf',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'pdf' ),
					'permission_callback' => array( self::class, 'can_export' ),
					'args'                => $args,
				)
			);
		}
	}

	public static function can_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	private static function get_data( int $org_id, string $period ): array {
		return SnapshotBuilder::build( $org_id, $period );
	}

	public static function summary( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$org_id = absint( $req['org_id'] );
		$period = sanitize_text_field( $req['period'] );
		$data   = self::get_data( $org_id, $period );
		return \rest_ensure_response( $data );
	}

	public static function csv( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$org_id        = absint( $req['org_id'] );
		$period        = sanitize_text_field( $req['period'] );
		$data          = self::get_data( $org_id, $period );
				$title = sprintf( esc_html__( '%1$s Snapshot', 'artpulse' ), esc_html( $period ) );

		$path = SnapshotBuilder::generate_csv(
			array(
				'title' => $title,
				'data'  => $data,
			)
		);
		$csv  = file_get_contents( $path );
		FileSystem::safe_unlink( $path );
		return new WP_REST_Response(
			$csv,
			200,
			array(
				'Content-Type'        => 'text/csv',
				'Content-Disposition' => 'attachment; filename="snapshot.csv"',
			)
		);
	}

	public static function pdf( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$org_id        = absint( $req['org_id'] );
		$period        = sanitize_text_field( $req['period'] );
		$data          = self::get_data( $org_id, $period );
				$title = sprintf( esc_html__( '%1$s Snapshot', 'artpulse' ), esc_html( $period ) );

		$path = SnapshotBuilder::generate_pdf(
			array(
				'title' => $title,
				'data'  => $data,
			)
		);
		$pdf  = file_get_contents( $path );
		FileSystem::safe_unlink( $path );
		return new WP_REST_Response(
			$pdf,
			200,
			array(
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="snapshot.pdf"',
			)
		);
	}
}
