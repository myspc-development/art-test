<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class StatusController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		register_rest_route(
			'artpulse/v1',
			'/status',
			array(
                                array(
                                        'methods'             => WP_REST_Server::READABLE,
                                        'callback'            => array( self::class, 'get_status' ),
                                        'permission_callback' => array( Auth::class, 'guard_manage' ),
                                ),
			)
		);
	}

	public static function get_status(): WP_REST_Response|WP_Error {
		$plugin_version = defined( 'ARTPULSE_VERSION' ) ? ARTPULSE_VERSION : '1.0.0';
		$db_version     = get_option( 'artpulse_db_version', '0.0.0' );
		$cache          = ( defined( 'WP_CACHE' ) && WP_CACHE ) ? 'Enabled' : 'Disabled';
		$debug          = defined( 'WP_DEBUG' ) && WP_DEBUG;

		return \rest_ensure_response(
			array(
				'plugin_version' => $plugin_version,
				'db_version'     => $db_version,
				'cache'          => $cache,
				'debug'          => $debug,
			)
		);
	}
}
