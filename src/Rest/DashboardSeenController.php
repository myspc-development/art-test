<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
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
                                'permission_callback' => array( Auth::class, 'guard_read' ),
                        )
                );
        }

	public static function mark_seen( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		update_user_meta( get_current_user_id(), 'ap_seen_dashboard_v2', 1 );
		return \rest_ensure_response( array( 'seen' => true ) );
	}
}
