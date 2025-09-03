<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\RestResponder;
// Use the dashboard builder registry rather than the core registry
// so we can query widgets and render previews for the builder UI.
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Support\WidgetIds;
use ArtPulse\Rest\Util\Auth;

/**
 * REST controller for the Dashboard Builder.
 */
class DashboardWidgetController {
        use RestResponder;

	/**
	 * Convert an array of builder layout items to core widget IDs.
	 * Logs a warning if a widget ID cannot be mapped.
	 *
	 * @param array<int,array|string> $layout
	 * @return array<int,array>
	 */
        private static function convert_to_core_ids( array $layout ): array {
                $converted = array();
                foreach ( $layout as $item ) {
                        if ( is_array( $item ) ) {
                                $id  = WidgetIds::canonicalize( $item['id'] ?? '' );
                                $vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
                        } else {
                                $id  = WidgetIds::canonicalize( (string) $item );
                                $vis = true;
                        }

                        $core = self::to_core_id( $id );
                        $core = DashboardWidgetRegistry::canon_slug( $core );

                        if ( $id && ! DashboardWidgetRegistry::exists( $core ) ) {
                                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                                        error_log( '[DashboardBuilder] Unmapped widget ID: ' . $id );
                                }
                                continue;
                        }

                        $converted[] = array(
                                'id'      => $core,
                                'visible' => $vis,
                        );
                }

                return $converted;
        }

	/**
	 * Convert an array of core layout items to builder widget IDs.
	 *
	 * @param array<int,array|string> $layout
	 * @return array<int,array>
	 */
	private static function convert_to_builder_ids( array $layout ): array {
		$converted = array();
		foreach ( $layout as $item ) {
			if ( is_array( $item ) ) {
				$id  = sanitize_key( $item['id'] ?? '' );
				$vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
			} else {
				$id  = sanitize_key( (string) $item );
				$vis = true;
			}

			$converted[] = array(
				'id'      => self::to_builder_id( $id ),
				'visible' => $vis,
			);
		}

		return $converted;
	}

	/**
	 * Convert a builder widget ID to the core ID.
	 */
	private static function to_core_id( string $id ): string {
		return \ArtPulse\Core\DashboardWidgetRegistry::map_to_core_id( $id );
	}

	/**
	 * Convert a core widget ID to the builder ID.
	 */
	private static function to_builder_id( string $id ): string {
		return \ArtPulse\Core\DashboardWidgetRegistry::map_to_builder_id( $id );
	}
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-widgets' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard-widgets',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'get_widgets' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'edit_posts' ),
                                )
                        );
                }
                if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-widgets/save' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/dashboard-widgets/save',
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'save_layout' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'edit_posts' ),
                                )
                        );
                }
                if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-widgets/export' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/dashboard-widgets/export',
                                array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'export_layout' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'edit_posts' ),
                                )
                        );
                }
                if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-widgets/import' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/dashboard-widgets/import',
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'import_layout' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'edit_posts' ),
                                )
                        );
                }
        }

        public static function get_widgets( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $guard = \ArtPulse\Rest\Util\Auth::guard( $request, 'edit_posts' );
                if ( is_wp_error( $guard ) ) {
                        return $guard;
                }
		$role        = sanitize_key( $request->get_param( 'role' ) );
		$include_all = filter_var( $request->get_param( 'include_all' ), FILTER_VALIDATE_BOOLEAN );
		if ( ! $role ) {
			return new WP_Error( 'invalid_role', __( 'Role parameter missing', 'artpulse' ), array( 'status' => 400 ) );
		}

		$simulate = filter_var( $request->get_param( 'simulate' ), FILTER_VALIDATE_BOOLEAN );
		$widgets  = DashboardWidgetTools::listWidgetsForRole( $role, $simulate );

		$available = array_values( array_filter( $widgets, static fn( $w ) => $w['is_allowed_for_role'] ) );

		if ( empty( $available ) && ! get_role( $role ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Dashboard widgets request for unsupported role: ' . $role );
			}
			return new WP_Error( 'invalid_role', __( 'Unsupported role', 'artpulse' ), array( 'status' => 400 ) );
		}
		// Previews are generated in listWidgetsForRole.

		$core_result   = \ArtPulse\Admin\UserLayoutManager::get_role_layout( $role );
		$core_layout   = $core_result['layout'];
		$active_layout = self::convert_to_builder_ids( $core_layout );

		$response = array(
			'available' => $available,
			'active'    => array(
				'role'   => $role,
				'layout' => $active_layout,
			),
		);

		if ( $include_all ) {
			$response['all'] = $widgets;
		}

		return \rest_ensure_response( $response );
	}

        public static function save_layout( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $guard = Auth::guard( $request, 'edit_posts', 'ap_save_layout' );
                if ( is_wp_error( $guard ) ) {
                        return $guard;
                }

               $data = $request->get_json_params();
               if ( empty( $data ) ) {
                       $data = $request->get_body_params();
               }
               // Support both JSON and form-encoded requests.
               $role = sanitize_key( $data['role'] ?? '' );
               if ( ! $role ) {
                       return new WP_Error( 'invalid_role', __( 'Invalid role', 'artpulse' ), array( 'status' => 400 ) );
               }
		if ( empty( DashboardWidgetRegistry::get_for_role( $role ) ) && ! get_role( $role ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Attempt to save dashboard layout for unsupported role: ' . $role );
			}
			return new WP_Error( 'invalid_role', __( 'Unsupported role', 'artpulse' ), array( 'status' => 400 ) );
		}
               $layout = $data['layout'] ?? null;
               if ( isset( $layout ) && is_array( $layout ) ) {
                       $layout = self::convert_to_core_ids( $layout );
                       \ArtPulse\Admin\UserLayoutManager::save_role_layout( $role, $layout );
               } else {
                       $order   = array_map( 'sanitize_key', (array) ( $data['layoutOrder'] ?? array() ) );
                        $layout  = self::convert_to_core_ids(
                                array_map(
                                        fn( $id ) => array(
                                                'id'      => $id,
                                                'visible' => true,
                                        ),
                                        $order
                                )
                        );
                        \ArtPulse\Admin\UserLayoutManager::save_role_layout( $role, $layout );
                }

                return ( new self() )->ok( array( 'saved' => true ) );
        }


                       return $nonce_check;
               }

               $cap_check = Auth::require_cap( 'edit_posts' );
               if ( is_wp_error( $cap_check ) ) {


}
