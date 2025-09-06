<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class ArtistOverviewController {
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
			'/artist',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_overview' ),
					'permission_callback' => array( Auth::class, 'guard_read' ),
				),
			)
		);
	}

	public static function get_overview(): WP_REST_Response|WP_Error {
		$user_id   = get_current_user_id();
		$followers = (int) get_user_meta( $user_id, 'ap_follower_count', true );
		$sales     = (int) get_user_meta( $user_id, 'ap_total_sales', true );
		$artworks  = count_user_posts( $user_id, 'artpulse_artwork' );

		return \rest_ensure_response(
			array(
				'followers' => $followers,
				'sales'     => $sales,
				'artworks'  => $artworks,
			)
		);
	}
}
