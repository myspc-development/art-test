<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Core\ActivityLogger;
use ArtPulse\Rest\Util\Auth;

class ActivityRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/activity' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/activity',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'list' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'limit' => array(
							'type'    => 'integer',
							'default' => 20,
						),
					),
				)
			);
		}
	}

	public static function list( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$org_id  = (int) get_user_meta( $user_id, 'ap_organization_id', true );
		$limit   = absint( $request['limit'] ) ?: 20;

		$logs = ActivityLogger::get_logs( $org_id ?: null, $user_id, $limit );
		return \rest_ensure_response( $logs );
	}
}
