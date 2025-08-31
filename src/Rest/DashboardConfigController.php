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
                                'permission_callback' => Auth::require_login_and_cap( 'manage_options' ),
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

       /**
        * Assign a value to a map using a canonical widget ID.
        *
        * Canonicalizes the provided ID, verifies the widget exists and merges
        * duplicate array values when encountered. Scalar values retain the first
        * assignment.
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
                       $seen = array();
                       $items = array_values(
                               array_filter(
                                       array_map(
                                               static function ( $item ) use ( $to_canon, &$seen ) {
                                                       $id = is_array( $item ) && isset( $item['id'] ) ? $item['id'] : $item;
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

               $capabilities = array();
               $excluded     = array();
               $processed    = array();
               foreach ( DashboardWidgetRegistry::get_all( null, false, true ) as $id => $def ) {
                       $cid = $to_canon( $id );
                       if ( $cid === '' || isset( $processed[ $cid ] ) ) {
                               continue;
                       }
                       $processed[ $cid ] = true;

                       if ( ! empty( $def['capability'] ) ) {
                               $capabilities[ $cid ] = sanitize_key( $def['capability'] );
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
                       'capabilities'   => $capabilities,
                       'excluded_roles' => $excluded,
               );

               return new \WP_REST_Response( $payload );
       }
       public static function save_config( WP_REST_Request $request ) {
               $nonce = $request->get_header( 'X-AP-Nonce' );
               if ( ! wp_verify_nonce( $nonce, 'ap_dashboard_config' ) ) {
                       return new WP_Error( 'rest_forbidden', 'Invalid nonce.', array( 'status' => 403 ) );
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
