<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Core\UserDashboardManager;

/**
 * Simplified REST controller for user dashboard layouts.
 */
class DashboardLayoutRestController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard/layout' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard/layout',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'get_layout' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'role' => array(
								'type'     => 'string',
								'required' => false,
							),
						),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'save_layout' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'layout'     => array(
								'type'     => 'array',
								'required' => false,
							),
							'visibility' => array(
								'type'     => 'object',
								'required' => false,
							),
						),
					),
				)
			);
		}
	}

	public static function get_layout( WP_REST_Request $request ): WP_REST_Response {
		return UserDashboardManager::getDashboardLayout( $request );
	}

	public static function save_layout( WP_REST_Request $request ): WP_REST_Response {
		return UserDashboardManager::saveDashboardLayout( $request );
	}
}
