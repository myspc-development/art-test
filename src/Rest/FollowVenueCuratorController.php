<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class FollowVenueCuratorController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/follow/venue' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/follow/venue',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'follow_venue' ),
					'permission_callback' => Auth::require_login_and_cap( null ),
					'args'                => array(
						'venue_id' => array(
							'type'     => 'integer',
							'required' => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/followed/venues' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/followed/venues',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_followed_venues' ),
					'permission_callback' => array( Auth::class, 'guard_read' ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/follow/curator' ) ) {
				register_rest_route(
					ARTPULSE_API_NAMESPACE,
					'/follow/curator',
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'follow_curator' ),
						'permission_callback' => Auth::require_login_and_cap( null ),
						'args'                => array(
							'curator_id' => array(
								'type'     => 'integer',
								'required' => true,
							),
						),
					)
				);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/followed/curators' ) ) {
				register_rest_route(
					ARTPULSE_API_NAMESPACE,
					'/followed/curators',
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'get_followed_curators' ),
						'permission_callback' => array( Auth::class, 'guard_read' ),
					)
				);
		}
	}

	public static function follow_venue( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$user_id  = get_current_user_id();
		$venue_id = absint( $req['venue_id'] );
		$list     = get_user_meta( $user_id, 'ap_following_venues', true );
		$list     = is_array( $list ) ? $list : array();
		if ( ! in_array( $venue_id, $list, true ) ) {
			$list[] = $venue_id;
			update_user_meta( $user_id, 'ap_following_venues', $list );
		}
		return \rest_ensure_response( array( 'venues' => array_map( 'intval', $list ) ) );
	}

	public static function get_followed_venues(): WP_REST_Response|WP_Error {
		$list = get_user_meta( get_current_user_id(), 'ap_following_venues', true );
		$list = is_array( $list ) ? array_map( 'intval', $list ) : array();
		return \rest_ensure_response( $list );
	}

	public static function follow_curator( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$user_id    = get_current_user_id();
		$curator_id = absint( $req['curator_id'] );
		$list       = get_user_meta( $user_id, 'ap_following_curators', true );
		$list       = is_array( $list ) ? $list : array();
		if ( ! in_array( $curator_id, $list, true ) ) {
			$list[] = $curator_id;
			update_user_meta( $user_id, 'ap_following_curators', $list );
		}
		return \rest_ensure_response( array( 'curators' => array_map( 'intval', $list ) ) );
	}

	public static function get_followed_curators(): WP_REST_Response|WP_Error {
		$list = get_user_meta( get_current_user_id(), 'ap_following_curators', true );
		$list = is_array( $list ) ? array_map( 'intval', $list ) : array();
		return \rest_ensure_response( $list );
	}
}
