<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\DashboardAnalyticsLogger;
use ArtPulse\Rest\RestResponder;

class DashboardAnalyticsController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-analytics' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard-analytics',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'log_event' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
				)
			);
		}
	}

        public static function check_permissions( WP_REST_Request $request ) {
                $nonce = $request->get_header( 'X-WP-Nonce' );
                $check = \ArtPulse\Rest\Util\Auth::verify_nonce( $nonce );
                if ( is_wp_error( $check ) ) {
                        return $check;
                }
                return current_user_can( 'read' ) ? true : new WP_Error( 'rest_forbidden', 'Forbidden.', array( 'status' => 403 ) );
        }

	public static function log_event( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event   = sanitize_text_field( $request['event'] ?? '' );
		$details = sanitize_text_field( $request['details'] ?? '' );
		if ( $event === '' ) {
			return new WP_Error( 'invalid_event', 'Event required', array( 'status' => 400 ) );
		}
		DashboardAnalyticsLogger::log( get_current_user_id(), $event, $details );
		return \rest_ensure_response( array( 'logged' => true ) );
	}
}
