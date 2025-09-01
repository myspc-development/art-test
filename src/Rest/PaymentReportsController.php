<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Admin\PaymentAnalyticsDashboard;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class PaymentReportsController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/payment-reports' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/payment-reports',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'get_reports' ),
                                        'permission_callback' => array( Auth::class, 'guard_manage' ),
				)
			);
		}
	}

	public static function get_reports( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$start = sanitize_text_field( $request->get_param( 'start_date' ) ?? '' );
		$end   = sanitize_text_field( $request->get_param( 'end_date' ) ?? '' );

		$metrics = PaymentAnalyticsDashboard::get_metrics( $start, $end );

		return \rest_ensure_response( array( 'metrics' => $metrics ) );
	}
}
