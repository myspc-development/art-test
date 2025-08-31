<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Rest\Util\Auth;

/**
 * Endpoints used by the React widget editor UI.
 */
class WidgetEditorController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		// List available widgets (read-only).
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/widgets',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_widgets' ),
				'permission_callback' => static fn () => current_user_can( 'read' ),
			)
		);

		// List WordPress roles (read-only).
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/roles',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_roles' ),
				// Allow any authenticated user with basic read capability.
				'permission_callback' => array( Auth::class, 'guard_read' ),
			)
		);

		// Get/Save layout for a role (admin only).
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/layout',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_layout' ),
					'permission_callback' => static fn () => current_user_can( 'manage_options' ),
					'args'                => array(
						'role' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'save_layout' ),
					'permission_callback' => static fn () => current_user_can( 'manage_options' ),
					'args'                => array(
						'role'   => array(
							'type'     => 'string',
							'required' => true,
						),
						'layout' => array(
							'type'     => 'array',
							'required' => true,
						),
						'style'  => array(
							'type'     => 'object',
							'required' => false,
						),
					),
				),
			)
		);
	}

	public static function get_widgets(): WP_REST_Response {
		$defs = DashboardWidgetManager::getWidgetDefinitions( true );
		return rest_ensure_response( array_values( $defs ) );
	}

	public static function get_roles(): WP_REST_Response {
		$roles = array_keys( wp_roles()->roles );
		return rest_ensure_response( array_values( $roles ) );
	}

	public static function get_layout( WP_REST_Request $req ): WP_REST_Response {
		$role   = sanitize_key( (string) $req['role'] );
		$result = DashboardWidgetManager::getRoleLayout( $role );
		$layout = $result['layout'] ?? array();
		$style  = \ArtPulse\Admin\UserLayoutManager::get_role_style( $role );

		return rest_ensure_response(
			array(
				'layout' => $layout,
				'style'  => is_array( $style ) ? $style : array(),
			)
		);
	}

	public static function save_layout( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$role = sanitize_key( (string) $req['role'] );

		$data   = $req->get_json_params();
		$layout = isset( $data['layout'] ) && is_array( $data['layout'] ) ? $data['layout'] : null;
		if ( $layout === null ) {
			return new WP_Error( 'invalid_layout', 'Invalid layout payload.', array( 'status' => 400 ) );
		}

		$style = isset( $data['style'] ) && is_array( $data['style'] ) ? $data['style'] : array();

		DashboardWidgetManager::saveRoleLayout( $role, $layout );
		if ( $style ) {
			\ArtPulse\Admin\UserLayoutManager::save_role_style( $role, $style );
		}

		return rest_ensure_response( array( 'saved' => true ) );
	}

	public static function handle_layout( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		return match ( $req->get_method() ) {
			'POST'   => self::save_layout( $req ),
			'GET'    => self::get_layout( $req ),
			default  => new WP_Error( 'invalid_method', 'Method not allowed', array( 'status' => 405 ) ),
		};
	}
}
