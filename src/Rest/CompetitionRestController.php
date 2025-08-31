<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\CompetitionEntryManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CompetitionRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/competitions/(?P<id>\d+)/entries' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/competitions/(?P<id>\d+)/entries',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'submit_entry' ),
					'permission_callback' => array( self::class, 'can_submit' ),
					'args'                => array(
						'id'         => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'artwork_id' => array(
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
							'required'          => true,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/competitions/(?P<id>\d+)/entries/(?P<entry_id>\d+)/vote' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/competitions/(?P<id>\d+)/entries/(?P<entry_id>\d+)/vote',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'vote_entry' ),
					'permission_callback' => array( self::class, 'can_vote' ),
					'args'                => array(
						'id'       => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'entry_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}
	}

	public static function can_submit( WP_REST_Request $req ): bool {
		return is_user_logged_in();
	}

	public static function submit_entry( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$comp_id    = absint( $req['id'] );
		$artwork_id = absint( $req['artwork_id'] );
		$user_id    = get_current_user_id();

		$artwork = get_post( $artwork_id );
		if ( ! $artwork || $artwork->post_type !== 'artpulse_artwork' ) {
			return new WP_Error( 'invalid_artwork', 'Invalid artwork.', array( 'status' => 400 ) );
		}
		if ( (int) $artwork->post_author !== $user_id && ! current_user_can( 'edit_others_posts' ) ) {
			return new WP_Error( 'forbidden', 'You cannot submit this artwork.', array( 'status' => 403 ) );
		}

		CompetitionEntryManager::add_entry( $comp_id, $artwork_id, $user_id );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function can_vote( WP_REST_Request $req ): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$comp_id = absint( $req['id'] );
		$method  = get_post_meta( $comp_id, 'voting_method', true ) ?: 'community';
		if ( $method === 'jury' ) {
			return current_user_can( 'manage_options' );
		}
		return true;
	}

	public static function vote_entry( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$entry_id = absint( $req['entry_id'] );
		$count    = CompetitionEntryManager::vote( $entry_id, get_current_user_id() );
		return \rest_ensure_response(
			array(
				'success' => true,
				'votes'   => $count,
			)
		);
	}
}
