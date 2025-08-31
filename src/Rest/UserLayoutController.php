<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

// Ensure this controller checks current user and validates role/widget IDs:
// - Only for logged-in users.
// - Sanitize role in ['member','artist','organization','admin'].
// - Filter incoming layout to allowed widget IDs.
// - update_user_meta(get_current_user_id(), "ap_layout_{$role}", $layout).
class UserLayoutController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/user/layout',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_layout' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'save_layout' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
				),
			)
		);
	}

	public static function check_permissions( WP_REST_Request $request ): bool {
		return is_user_logged_in();
	}

	public static function get_layout( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$role   = sanitize_key( $request['role'] ?? '' );
		$meta   = $role ? get_user_meta( get_current_user_id(), 'ap_layout_' . $role, true ) : array();
		$layout = is_array( $meta ) ? array_values( array_filter( array_map( 'sanitize_key', $meta ) ) ) : array();
		return \rest_ensure_response( array( 'layout' => $layout ) );
	}

	public static function save_layout( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$role   = sanitize_key( $request['role'] ?? '' );
		$layout = $request['layout'] ?? array();
		if ( ! $role || ! is_array( $layout ) ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}
		$layout = array_values( array_filter( array_map( 'sanitize_key', $layout ) ) );
		update_user_meta( get_current_user_id(), 'ap_layout_' . $role, $layout );
		return \rest_ensure_response( array( 'saved' => true ) );
	}
}
