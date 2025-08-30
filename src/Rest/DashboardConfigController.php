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
						'permission_callback' => Auth::require_login_and_cap( 'read' ),
					),
                                        array(
                                                'methods'             => 'POST',
                                                'callback'            => array( self::class, 'save_config' ),
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
               $guard = Auth::guard_read( $request );
               if ( is_wp_error( $guard ) ) {
                       return $guard;
               }

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

               $defs         = DashboardWidgetRegistry::get_all();
               $capabilities = array();
               $excluded     = array();
               foreach ( $defs as $id => $def ) {
                       if ( ! empty( $def['capability'] ) ) {
                               $capabilities[ $id ] = sanitize_key( $def['capability'] );
                       }
                       if ( ! empty( $def['exclude_roles'] ) ) {
                               $excluded[ $id ] = array_map( 'sanitize_key', (array) $def['exclude_roles'] );
                       }
               }

               $payload = array(
                       'widget_roles'   => $visibility,
                       'role_widgets'   => $role_widgets,
                       'locked'         => array_values( $locked ),
                       'capabilities'   => $capabilities,
                       'excluded_roles' => $excluded,
               );

               return new \WP_REST_Response( $payload, 200 );
       }

	public static function save_config( WP_REST_Request $request ) {
                $guard = Auth::guard( $request, 'manage_options' );
                if ( is_wp_error( $guard ) ) {
                        return $guard;
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
