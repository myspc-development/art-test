<?php
namespace ArtPulse\Admin;

class DashboardLayoutEndpoint {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-layout/(?P<context>\\w+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard-layout/(?P<context>\\w+)',
				array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => fn () => current_user_can( 'manage_options' ),
				)
			);
		}
	}

	public static function handle( \WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'invalid_nonce', 'Invalid or missing nonce', array( 'status' => 403 ) );
		}

		$ctx    = sanitize_key( $request['context'] );
		$option = get_option( 'ap_dashboard_widget_config', array() );

		if ( $request->get_method() === 'GET' ) {
			$layout = $option[ $ctx ] ?? array();
			return \rest_ensure_response( $layout );
		}

		$layout = $request->get_json_params();
		if ( ! is_array( $layout ) ) {
			return new \WP_Error( 'invalid_layout', 'Layout must be an array', array( 'status' => 400 ) );
		}

		UserLayoutManager::save_role_layout( $ctx, $layout );

		return \rest_ensure_response( array( 'saved' => true ) );
	}
}
