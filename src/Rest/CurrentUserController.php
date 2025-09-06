<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_Error;
use WP_REST_Response;
use ArtPulse\Rest\RestResponder;

class CurrentUserController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/me' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/me',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_current_user' ),
					'permission_callback' => Auth::require_login_and_cap(
						static fn() => Auth::is_test_mode() || current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' )
					),
				)
			);
		}
	}

	public static function get_current_user(): WP_REST_Response|WP_Error {
			$user = wp_get_current_user();
			$data = array(
				'id'    => $user->ID,
				'role'  => $user->roles[0] ?? '',
				'roles' => $user->roles,
			);

			return ( new self() )->ok( $data );
	}
}
