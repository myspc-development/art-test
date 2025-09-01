<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;
use ArtPulse\Support\WidgetIds;
use ArtPulse\Rest\RestResponder;

class DashboardConfigController {
        use RestResponder;
    private static function verify_nonce( WP_REST_Request $request, string $action ): bool|WP_Error {
        return Auth::verify_nonce( $request->get_header( 'X-AP-Nonce' ), $action );
    }
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
                        'permission_callback' => array( Auth::class, 'guard_read' ),
                    ),
                    array(
                        'methods'             => 'POST',
                        'callback'            => array( self::class, 'save_config' ),
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
     * Assign a canonicalized widget id => value into a map (merging arrays).
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
        $role_widgets = OptionUtils::get_array_option( 'artpulse_role_widgets' );
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
            return DashboardWidgetRegistry::canon_slug( $core ); // ensures widget_ prefix
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

        // Normalize visibility & role widget lists to canonical widget_* ids.
        foreach ( $visibility as $role => &$ids ) {
            $ids = $canon_unique( $ids );
        }
        unset( $ids );

        foreach ( $role_widgets as $role => &$ids ) {
            $ids = $canon_unique( $ids );
        }
        unset( $ids );

        $locked = $canon_unique( $locked );

        // Normalize layout maps to canonical ids as well (if stored).
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
                            return is_array( $item )
                                ? array_merge( $item, array( 'id' => $cid ) )
                                : array( 'id' => $cid );
                        },
                        (array) $items
                    )
                )
            );
        }
        unset( $items );

        // Build capabilities map strictly using canonical widget_* ids.
        $capabilities_raw = array();
        foreach ( DashboardWidgetRegistry::get_all() as $id => $def ) {
            $cid = $to_canon( $id );
            if ( $cid === '' ) {
                continue;
            }
            if ( ! empty( $def['capability'] ) ) {
                $capabilities_raw[ $cid ] = sanitize_key( $def['capability'] );
            }
        }

        // Re-normalize in case anything slipped in without prefix or duplicates.
        $capabilities = array();
        foreach ( $capabilities_raw as $id => $cap ) {
            $cid = $to_canon( $id );
            if ( $cid !== '' ) {
                $capabilities[ $cid ] = $cap;
            }
        }
        ksort( $capabilities );

        // Optional: excluded roles per widget (canonicalized)
        $excluded = array();
        foreach ( DashboardWidgetRegistry::get_all() as $id => $def ) {
            $cid = $to_canon( $id );
            if ( $cid === '' ) {
                continue;
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
            'capabilities'   => $capabilities,     // keys like widget_one, widget_artpulse_analytics_widget
            'excluded_roles' => $excluded,
        );

        return new WP_REST_Response( $payload );
    }

    public static function save_config( WP_REST_Request $request ) {
        $nonce_check = self::verify_nonce( $request, 'ap_dashboard_config' );
        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'rest_forbidden', __( 'Insufficient permissions.', 'artpulse' ), array( 'status' => 403 ) );
        }

        $data = $request->get_json_params();

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
