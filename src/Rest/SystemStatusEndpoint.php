<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

final class SystemStatusEndpoint {
    public static function register(): void {
        if ( did_action( 'rest_api_init' ) ) {
            self::register_routes();
        } else {
            add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
        }
    }

    public static function register_routes(): void {
        if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/system/status' ) ) {
            register_rest_route(
                ARTPULSE_API_NAMESPACE,
                '/system/status',
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => function () {
                        return Auth::require_cap( 'read' );
                    },
                    'callback'            => array( self::class, 'get_status' ),
                )
            );
        }
    }

    public static function get_status(): \WP_REST_Response {
        global $wp_version;

        return \rest_ensure_response(
            array(
                'plugin'    => defined( 'ARTPULSE_VERSION' ) ? ARTPULSE_VERSION : null,
                'wordpress' => $wp_version,
            )
        );
    }
}
