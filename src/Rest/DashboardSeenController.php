<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class DashboardSeenController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/user/seen-dashboard-v2',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'mark_seen' ),
				'permission_callback' => array( self::class, 'check_permissions' ),
			)
		);
	}

	public static function check_permissions( WP_REST_Request $request ): bool {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return is_user_logged_in() && $nonce && wp_verify_nonce( $nonce, 'wp_rest' );
	}

	public static function mark_seen( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		update_user_meta( get_current_user_id(), 'ap_seen_dashboard_v2', 1 );
		return rest_ensure_response( array( 'seen' => true ) );
	}
}
