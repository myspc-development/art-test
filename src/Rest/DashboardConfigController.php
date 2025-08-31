<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;
use ArtPulse\Support\WidgetIds;

class DashboardConfigController {
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/dashboard-config' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/dashboard-config',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'get_config' ),
						'permission_callback' => function () {
							// Keep GET simple: require 'read' → 403 when missing.
							return current_user_can( 'read' )
								? true
								: new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
						},
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'save_config' ),
						'permission_callback' => function () {
							// Enforce capability FIRST (tests expect 403 before nonce checks).
							return current_user_can( 'manage_options' )
								? true
								: new WP_Error( 'rest_forbidden', 'Sorry, you are not allowed to do that.', array( 'status' => 403 ) );
						},
						'args'                => array(
							'widget_roles' => array(
								'type'     => 'object',
								'required' => false,
							),
							'role_widgets' => array(
								'type'     => 'object',
								'required' => false,
							),
							'layout' => array(
								'type'     => 'object',
								'required' => false,
							),
							'locked' => array(
								'type'     => 'array',
								'required' => false,
							),
						),
					),
				)
			);
		}
	}

	/**
	 * Assign a value to a map using a canonical widget ID.
	 * (Kept for compatibility if you later need it.)
	 */
	private static function assign_canonical( array &$map, $id, $value ): void {
		$cid = WidgetIds::canonicalize( $id );
		if ( $cid === '' || ! DashboardWidgetRegistry::exists( $cid ) ) {
			return;
		}

		if ( isset( $map[ $cid ] ) ) {
			if ( is_array( $map[ $cid ] ) && is_array( $value ) ) {
				$map[ $cid ] = array_values( array_unique( array_merge( $map[ $cid ], $value ) ) );
			}
			return;
		}

		$map[ $cid ] = $value;
	}

	public static function get_config( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$visibility   = OptionUtils::get_array_option( 'artpulse_widget_roles' );
		$locked       = get_option( 'artpulse_locked_widgets', array() );
		$role_widgets = OptionUtils::get_array_option( 'artpulse_dashboard_layouts' );

		if ( ! $role_widgets ) {
			$role_widgets = array();
			foreach ( DashboardWidgetRegistry::get_role_widget_map() as $role => $widgets ) {
				$role_widgets[ $role ] = array_values(
					array_map(
						static fn( $w ) => sanitize_key( $w['id'] ?? '' ),
						$widgets
					)
				);
			}
		}

		$to_canon = static function ( $id ) {
			$core = DashboardWidgetRegistry::map_to_core_id( (string) $id );
			return DashboardWidgetRegistry::canon_slug( $core );
		};

		$canon_unique = static function ( $ids ) use ( $to_canon ) {
			$seen = array();
			foreach ( (array) $ids as $id ) {
				$cid = $to_canon( $id );
				if ( $cid === '' || isset( $seen[ $cid ] ) ) {
					continue;
				}
				$seen[ $cid ] = true;
			}
			return array_keys( $seen );
		};

		foreach ( $visibility as $role => &$ids ) {
			$ids = $canon_unique( $ids );
		}
		unset( $ids );

		foreach ( $role_widgets as $role => &$ids ) {
			$ids = $canon_unique( $ids );
		}
		unset( $ids );

		$locked = $canon_unique( $locked );

		$layout = OptionUtils::get_array_option( 'artpulse_dashboard_layouts' );
		foreach ( $layout as $role => &$items ) {
			$seen  = array();
			$items = array_values(
				array_filter(
					array_map(
						static function ( $item ) use ( $to_canon, &$seen ) {
							$id  = is_array( $item ) && isset( $item['id'] ) ? $item['id'] : $item;
							$cid = $to_canon( $id );
							if ( $cid === '' || isset( $seen[ $cid ] ) ) {
								return null;
							}
							$seen[ $cid ] = true;
							return is_array( $item ) ? array_merge( $item, array( 'id' => $cid ) ) : array( 'id' => $cid );
						},
						(array) $items
					)
				)
			);
		}
		unset( $items );

		// Build capability map keyed by canonical widget_* IDs.
		$capabilities = array();
		$excluded     = array();

		// Use all() so tests that register exactly two widgets keep a tight payload.
		foreach ( DashboardWidgetRegistry::all() as $id => $def ) {
			$cid = $to_canon( $id );
			if ( $cid === '' ) {
				continue;
			}
			if ( ! empty( $def['capability'] ) ) {
				$capabilities[ $cid ] = sanitize_key( (string) $def['capability'] );
			}
			if ( ! empty( $def['exclude_roles'] ) ) {
				$roles = array_values( array_unique( array_map( 'sanitize_key', (array) $def['exclude_roles'] ) ) );
				if ( $roles ) {
					$excluded[ $cid ] = $roles;
				}
			}
		}

		$payload = array(
			'widget_roles'   => $visibility,
			'role_widgets'   => $role_widgets,
			'layout'         => $layout,
			'locked'         => $locked,
			'capabilities'   => $capabilities,   // ← now canonical keys (e.g. widget_one)
			'excluded_roles' => $excluded,
		);

		return new WP_REST_Response( $payload );
	}

	public static function save_config( WP_REST_Request $request ) {
		// Since permission_callback has already enforced capability (403),
		// handle nonce here (401) to satisfy test expectations/order.
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );
		if ( $nonce === '' ) {
			// also accept parameter form as a fallback
			$nonce = (string) $request->get_param( '_wpnonce' );
		}
		if ( $nonce === '' || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'rest_invalid_nonce',
				__( 'Invalid or missing nonce.', 'artpulse' ),
				array( 'status' => 401 )
			);
		}

		$data       = (array) $request->get_json_params();
		$visibility = isset( $data['widget_roles'] ) && is_array( $data['widget_roles'] ) ? $data['widget_roles'] : array();

		foreach ( $visibility as $role => &$ids ) {
			$ids = array_values( array_unique( array_map( array( WidgetIds::class, 'canonicalize' ), (array) $ids ) ) );
		}
		unset( $ids );

		$layout = array();
		if ( isset( $data['layout'] ) && is_array( $data['layout'] ) ) {
			$layout = $data['layout'];
		} elseif ( isset( $data['role_widgets'] ) && is_array( $data['role_widgets'] ) ) {
			$layout = $data['role_widgets'];
		}

		foreach ( $layout as $role => &$ids ) {
			$ids = array_values(
				array_unique(
					array_filter(
						array_map( array( WidgetIds::class, 'canonicalize' ), (array) $ids ),
						array( DashboardWidgetRegistry::class, 'exists' )
					)
				)
			);
		}
		unset( $ids );

		$locked = isset( $data['locked'] ) && is_array( $data['locked'] ) ? $data['locked'] : array();
		$locked = array_values( array_unique( array_map( array( WidgetIds::class, 'canonicalize' ), (array) $locked ) ) );

		update_option( 'artpulse_widget_roles', $visibility );
		update_option( 'artpulse_dashboard_layouts', $layout );
		update_option( 'artpulse_locked_widgets', $locked );

		return \rest_ensure_response( array( 'saved' => true ) );
	}
}
