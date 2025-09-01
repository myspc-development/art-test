<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

final class SystemStatusController {
	use RestResponder;
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}
       public static function routes(): void {
                register_rest_route(
                        'ap/v1',
                        '/system/status',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'permission_callback' => Auth::allow_public(),
                                'callback'            => array( self::class, 'get_status' ),
                        )
                );
                register_rest_route(
                        'ap/v1',
                        '/status',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'permission_callback' => '__return_true',
                                'callback'            => array( self::class, 'get_status' ),
                        )
                );
        }

       public static function get_status(): WP_REST_Response|WP_Error {
                global $wp_version;
                return \rest_ensure_response(
                        array(
                                'wordpress' => $wp_version,
                                'php'       => PHP_VERSION,
                                'plugin'    => ARTPULSE_VERSION,
                        )
                );
        }
}
