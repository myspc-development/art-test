<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;

class LeaderboardRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/leaderboards/most-helpful' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/leaderboards/most-helpful',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'most_helpful' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/leaderboards/most-upvoted' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/leaderboards/most-upvoted',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'most_upvoted' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}
	}

	public static function most_helpful( WP_REST_Request $req ): WP_REST_Response {
		$limit = $req->get_param( 'limit' ) ? absint( $req['limit'] ) : 5;
		$data  = LeaderboardManager::get_most_helpful( $limit );
		return \rest_ensure_response( $data );
	}

	public static function most_upvoted( WP_REST_Request $req ): WP_REST_Response {
		$limit = $req->get_param( 'limit' ) ? absint( $req['limit'] ) : 5;
		$data  = LeaderboardManager::get_most_upvoted( $limit );
		return \rest_ensure_response( $data );
	}
}
