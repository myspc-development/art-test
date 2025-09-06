<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Response;
use ArtPulse\Rest\RestResponder;

class DashboardPreviewController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/preview/dashboard' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/preview/dashboard',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_preview' ),
					'permission_callback' => \ArtPulse\Rest\Util\Auth::require_login_and_cap( 'manage_options' ),
				)
			);
		}
	}

	public static function get_preview(): WP_REST_Response|WP_Error {
			$data = array(
				'user'    => wp_get_current_user()->display_name,
				'widgets' => \ArtPulse\Admin\DashboardWidgetTools::get_role_widgets_for_current_user(),
			);

			return \rest_ensure_response( $data );
	}
}
