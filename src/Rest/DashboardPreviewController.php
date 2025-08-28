<?php
namespace ArtPulse\Rest;

class DashboardPreviewController {
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
					'permission_callback' => function () {
						if ( ! current_user_can( 'read' ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
				)
			);
		}
	}

	public static function get_preview() {
		return array(
			'user'    => wp_get_current_user()->display_name,
			'widgets' => \ArtPulse\Admin\DashboardWidgetTools::get_role_widgets_for_current_user(),
		);
	}
}
