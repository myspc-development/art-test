<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

final class SystemStatusController {
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
				'callback'            => function () {
					global $wp_version;
					return \rest_ensure_response(
						array(
							'wordpress' => $wp_version,
							'php'       => PHP_VERSION,
							'plugin'    => ARTPULSE_VERSION,
						)
					);
				},
			)
		);
	}
}
