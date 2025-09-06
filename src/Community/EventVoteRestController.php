<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventVoteRestController {
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/vote' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/vote',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'vote' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/votes' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/votes',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'count' ),
					'permission_callback' => '__return_true',
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/leaderboards/top-events' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/leaderboards/top-events',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'top_events' ),
					'permission_callback' => '__return_true',
				)
			);
		}
	}

	public static function vote( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$event_id = absint( $req['id'] );
		if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', 'Invalid event', array( 'status' => 404 ) );
		}
		$user_id = get_current_user_id();
		$count   = EventVoteManager::vote( $event_id, $user_id );
		return \rest_ensure_response( array( 'votes' => $count ) );
	}

	public static function count( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$event_id = absint( $req['id'] );
		if ( ! $event_id ) {
			return new WP_Error( 'invalid_event', 'Invalid event', array( 'status' => 404 ) );
		}
		$count = EventVoteManager::get_votes( $event_id );
		$voted = is_user_logged_in() && EventVoteManager::has_voted( $event_id, get_current_user_id() );
		return \rest_ensure_response(
			array(
				'votes' => $count,
				'voted' => $voted,
			)
		);
	}

	public static function top_events( WP_REST_Request $req ): WP_REST_Response {
		$limit = $req->get_param( 'limit' ) ? absint( $req['limit'] ) : 10;
		$list  = EventVoteManager::get_top_voted( $limit );
		return \rest_ensure_response( $list );
	}
}
