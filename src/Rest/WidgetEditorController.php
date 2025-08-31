<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Rest\Util\Auth;

if ( ! defined( 'ARTPULSE_API_NAMESPACE' ) ) {
	define( 'ARTPULSE_API_NAMESPACE', 'artpulse/v1' );
}

class WidgetEditorController {

	public static function register(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ], 99 ); // late, so de-duper keeps ours
	}

	public static function register_routes(): void {
                register_rest_route(
                        ARTPULSE_API_NAMESPACE,
                        '/widgets',
                        [
                                'methods'             => 'GET',
                                'callback'            => [ self::class, 'get_widgets' ],
                                'permission_callback' => Auth::require_login_and_cap( 'read' ),
                                'args'                => [
                                        'role' => [
                                                'sanitize_callback' => 'sanitize_key',
                                                'type'              => 'string',
                                        ],
                                ],
                        ]
                );

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/roles',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_roles' ],
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
			]
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/layout',
			[
				'methods'             => [ 'GET', 'POST' ],
				'callback'            => [ self::class, 'handle_layout' ],
				'permission_callback' => Auth::require_login_and_cap( 'manage_options' ),
			]
		);
	}



	public static function get_roles(): WP_REST_Response {
		global $wp_roles;
		$roles = $wp_roles ? array_keys( $wp_roles->roles ) : [];
                return \rest_ensure_response( array_values( $roles ) );
        }

        public static function get_layout( WP_REST_Request $req ): WP_REST_Response {
                $role   = sanitize_key( $req['role'] );
                $result = \ArtPulse\Core\DashboardWidgetManager::getRoleLayout( $role );
                $layout = $result['layout'] ?? [];
                return \rest_ensure_response( array( 'layout' => $layout ) );
        }

        public static function save_layout( WP_REST_Request $req ): WP_REST_Response|WP_Error {
                $role   = sanitize_key( $req['role'] );
                $data   = (array) $req->get_json_params();
                $layout = $data['layout'] ?? [];
                if ( ! is_array( $layout ) ) {
                        return new WP_Error( 'invalid', 'Invalid layout', [ 'status' => 400 ] );
                }
                \ArtPulse\Core\DashboardWidgetManager::saveRoleLayout( $role, $layout );
                return \rest_ensure_response( [ 'saved' => true ] );
        }

        public static function get_widgets( WP_REST_Request $req ): WP_REST_Response|WP_Error {
                $role    = sanitize_key( $req->get_param( 'role' ) );
                $widgets = DashboardWidgetManager::getWidgetDefinitions( true );
                if ( $role ) {
                        $widgets = array_values(
                                array_filter(
                                        $widgets,
                                        static function ( $def ) use ( $role ) {
                                                $roles = isset( $def['roles'] ) ? (array) $def['roles'] : [];
                                                return ! $roles || in_array( $role, $roles, true );
                                        }
                                )
                        );
                }
                return \rest_ensure_response( $widgets );
        }

        public static function handle_layout( WP_REST_Request $req ): WP_REST_Response|WP_Error {
                return match ( $req->get_method() ) {
                        'POST'  => self::save_layout( $req ),
                        'GET'   => self::get_layout( $req ),
                        default => new WP_Error( 'invalid_method', 'Method not allowed', [ 'status' => 405 ] ),
                };
        }
}
