<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class StatusController {
		use RestResponder;

	public static function register(): void {
			$controller = new self();
		if ( did_action( 'rest_api_init' ) ) {
				$controller->register_routes();
		} else {
				add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
		}
	}

	public function register_routes(): void {
			register_rest_route(
				'artpulse/v1',
				'/status',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_status' ),
						'permission_callback' => '__return_true',
					),
				)
			);
	}

	public function get_status(): WP_REST_Response|WP_Error {
			$plugin_version = defined( 'ARTPULSE_VERSION' ) ? ARTPULSE_VERSION : '1.0.0';
			$db_version     = get_option( 'artpulse_db_version', '0.0.0' );
			$cache          = ( defined( 'WP_CACHE' ) && WP_CACHE ) ? 'Enabled' : 'Disabled';
			$debug          = defined( 'WP_DEBUG' ) && WP_DEBUG;

			return $this->ok(
				array(
					'plugin_version' => $plugin_version,
					'db_version'     => $db_version,
					'cache'          => $cache,
					'debug'          => $debug,
				)
			);
	}
}
