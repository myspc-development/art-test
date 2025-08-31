<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\Util\Auth;

class UpdateDiagnosticsController {

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
			'/update/diagnostics',
			array(
                                array(
                                        'methods'             => WP_REST_Server::READABLE,
                                        'callback'            => array( self::class, 'get_diagnostics' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'update_plugins' ),
                                ),
			)
		);
	}

	public static function get_diagnostics(): WP_REST_Response {
		$repo = get_option( 'ap_github_repo_url' );
		if ( ! $repo ) {
			return rest_ensure_response( array( 'error' => 'No repo URL configured' ) );
		}

		$api  = str_replace( 'https://github.com/', 'https://api.github.com/repos/', rtrim( $repo, '/' ) ) . '/releases/latest';
		$resp = wp_remote_get( $api, array( 'timeout' => 10 ) );

		return rest_ensure_response(
			array(
				'repo'       => $repo,
				'api'        => $api,
				'http_code'  => wp_remote_retrieve_response_code( $resp ),
				'body'       => json_decode( wp_remote_retrieve_body( $resp ), true ),
				'checked_at' => current_time( 'mysql' ),
			)
		);
	}
}
