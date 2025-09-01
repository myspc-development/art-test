<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Crm\ContactModel;
use ArtPulse\Crm\DonationModel;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class GrantReportController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\\d+)/grant-report' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orgs/(?P<id>\\d+)/grant-report',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'export' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'id'     => array( 'validate_callback' => 'absint' ),
						'format' => array( 'default' => 'csv' ),
					),
				)
			);
		}
	}

	public static function export( WP_REST_Request $req ) {
		$org_id    = absint( $req['id'] );
		$donations = DonationModel::get_by_org( $org_id );
		$stream    = fopen( 'php://temp', 'w' );
		fputcsv( $stream, array( 'email', 'amount', 'date' ) );
		foreach ( $donations as $d ) {
			fputcsv( $stream, array( $d['user_id'], $d['amount'], $d['donated_at'] ) );
		}
		rewind( $stream );
		$csv = stream_get_contents( $stream );
		fclose( $stream );
		return new WP_REST_Response(
			$csv,
			200,
			array(
				'Content-Type'        => 'text/csv',
				'Content-Disposition' => 'attachment; filename="grant-report.csv"',
			)
		);
	}
}
