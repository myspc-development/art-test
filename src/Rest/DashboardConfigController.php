<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
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
                                                'methods'  => 'GET',
                                                'callback' => array( self::class, 'get_config' ),
                                                'permission_callback' => function () {
			return Auth::require_cap( 'read' ) === true
                                                                ? true
                                                                : new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
                                                },
                                        ),
                        array(
                                'methods'  => 'POST',
                                'callback' => array( self::class, 'save_config' ),
                                'permission_callback' => '__return_true',
                                'args'                => array(
                                                        'widget_roles' => array(
                                                                'type'     => 'object',
                                                                'required' => false,
                                                        ),
							'role_widgets' => array(
								'type'     => 'object',
								'required' => false,
							),
							'layout'       => array(
								'type'     => 'object',
								'required' => false,
							),
							'locked'       => array(
								'type'     => 'array',
								'required' => false,
							),
						),
					),
				)
			);
		}
	}

       public static function get_config( WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
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

               foreach ( $visibility as $role => &$ids ) {
                       $ids = array_values( array_map( $to_canon, (array) $ids ) );
               }
               unset( $ids );

               foreach ( $role_widgets as $role => &$ids ) {
                       $ids = array_values( array_map( $to_canon, (array) $ids ) );
               }
               unset( $ids );

               $locked = array_values( array_map( $to_canon, (array) $locked ) );

               $layout = OptionUtils::get_array_option( 'artpulse_dashboard_layouts' );
               foreach ( $layout as $role => &$items ) {
                       $items = array_map(
                               static function ( $item ) use ( $to_canon ) {
                                       if ( is_array( $item ) && isset( $item['id'] ) ) {
                                               $item['id'] = $to_canon( $item['id'] );
                                               return $item;
                                       }

                                       return array( 'id' => $to_canon( $item ) );
                               },
                               (array) $items
                       );
               }
               unset( $items );

               $defs         = DashboardWidgetRegistry::get_all();
               $capabilities = array();
               $excluded     = array();
               foreach ( $defs as $id => $def ) {
                       $id = DashboardWidgetRegistry::canon_slug( $id );

                       if ( isset( $capabilities[ $id ] ) || isset( $excluded[ $id ] ) ) {
                               continue;
                       }

                       if ( ! empty( $def['capability'] ) ) {
                               $capabilities[ $id ] = sanitize_key( $def['capability'] );
                       }

                       if ( ! empty( $def['exclude_roles'] ) ) {
                               $excluded[ $id ] = array_map( 'sanitize_key', (array) $def['exclude_roles'] );
                       }
               }

               // Re-key maps to ensure canonical widget IDs and drop duplicates.
               $capabilities = array_reduce(
                       array_keys( $capabilities ),
                       static function ( $out, $key ) use ( $capabilities ) {
                               $cid = WidgetIds::canonicalize( $key );
                               if ( $cid !== '' ) {
                                       $out[ $cid ] = $capabilities[ $key ];
                               }
                               return $out;
                       },
                       array()
               );
               $excluded     = array_reduce(
                       array_keys( $excluded ),
                       static function ( $out, $key ) use ( $excluded ) {
                               $cid = WidgetIds::canonicalize( $key );
                               if ( $cid !== '' ) {
                                       $out[ $cid ] = $excluded[ $key ];
                               }
                               return $out;
                       },
                       array()
               );

               $payload = array(
                       'widget_roles'   => $visibility,
                       'role_widgets'   => $role_widgets,
                       'layout'         => $layout,
                       'locked'         => $locked,
                       'capabilities'   => $capabilities,
                       'excluded_roles' => $excluded,
               );

               return new \WP_REST_Response( $payload );
       }
       public static function save_config( WP_REST_Request $request ) {
                if ( ! current_user_can( 'manage_options' ) ) {
                        return new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
                }

                $nonce = $request->get_header( 'X-WP-Nonce' );
                if ( ! wp_verify_nonce( $nonce, 'ap_dashboard_config' ) ) {
                        return new WP_Error( 'rest_invalid_nonce', 'Invalid nonce.', array( 'status' => 401 ) );
                }

                $data       = $request->get_json_params();
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

		return rest_ensure_response( array( 'saved' => true ) );
	}
}
