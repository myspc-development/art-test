<?php
namespace ArtPulse\Tests;

use WP_REST_Request;
use WP_REST_Server;
use WP_User;

class RestTestHelpers {
	public static function as_admin(): int {
		$login = 'admin_user';
		$id    = username_exists( $login );
		if ( ! $id ) {
			$id = wp_create_user( $login, 'password', $login . '@example.com' );
		}
		$user = new WP_User( (int) $id );
		$user->set_role( 'administrator' );
		wp_set_current_user( (int) $id );
		return (int) $id;
	}

	public static function as_user( string $role ): int {
		$login = $role . '_user';
		$id    = username_exists( $login );
		if ( ! $id ) {
			$id = wp_create_user( $login, 'password', $login . '@example.com' );
		}
		$user = new WP_User( (int) $id );
		$user->set_role( $role );
		wp_set_current_user( (int) $id );
		return (int) $id;
	}

	public static function dispatch( WP_REST_Request $request ) {
		$server = rest_get_server();
		if ( ! $server instanceof WP_REST_Server ) {
			$server                    = new WP_REST_Server();
			$GLOBALS['wp_rest_server'] = $server;
			do_action( 'rest_api_init', $server );
		}
		return $server->dispatch( $request );
	}
}
