<?php
namespace ArtPulse\Reporting;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Support\FileSystem;

class OrgReportController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\d+)/report' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orgs/(?P<id>\d+)/report',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'download' ),
					'permission_callback' => array( self::class, 'can_download' ),
					'args'                => array(
						'id'     => array( 'type' => 'integer' ),
						'type'   => array(
							'required'          => true,
							'validate_callback' => fn( $t ) => in_array( $t, array( 'engagement', 'donors', 'grant' ), true ),
						),
						'format' => array(
							'default'           => 'csv',
							'validate_callback' => fn( $f ) => in_array( $f, array( 'csv', 'pdf' ), true ),
						),
					),
				)
			);
		}
	}

	public static function can_download() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function download( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$org_id = absint( $request['id'] );
		$type   = sanitize_text_field( $request['type'] );
		$format = sanitize_text_field( $request['format'] );

		$data = array(
			'Org ID' => $org_id,
			'Type'   => $type,
			'From'   => $request['from'] ?? '',
			'To'     => $request['to'] ?? '',
		);

		if ( $format === 'csv' ) {
			$path    = SnapshotBuilder::generate_csv(
				array(
					'title' => 'Org Report',
					'data'  => $data,
				)
			);
			$content = file_get_contents( $path );
			FileSystem::safe_unlink( $path );
			return new WP_REST_Response(
				$content,
				200,
				array(
					'Content-Type'        => 'text/csv',
					'Content-Disposition' => 'attachment; filename="org-report.csv"',
				)
			);
		}

		$path    = SnapshotBuilder::generate_pdf(
			array(
				'title' => 'Org Report',
				'data'  => $data,
			)
		);
		$content = file_get_contents( $path );
		FileSystem::safe_unlink( $path );
		return new WP_REST_Response(
			$content,
			200,
			array(
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="org-report.pdf"',
			)
		);
	}
}
