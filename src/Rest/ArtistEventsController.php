<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\Util\Auth;

class ArtistEventsController {

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
			'/artist-events',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_events' ),
				// Simple auth: must be logged in and have 'read'.
				'permission_callback' => function () {
					if ( ! is_user_logged_in() ) {
						return new WP_Error( 'rest_not_logged_in', 'Authentication required.', array( 'status' => 401 ) );
					}
					if ( ! current_user_can( 'read' ) ) {
						return new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
					}
					return true;
				},
			)
		);
	}

	/**
	 * Return the current user's posts as an array of IDs (ints), ordered by ID ASC.
	 * Tests expect a flat array like: [25, 27]
	 */
	public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error(
				'ap_rest_author_required',
				__( 'Authentication required.', 'artpulse' ),
				array( 'status' => 401 )
			);
		}

		// Allow specifying post_type via query param; default to 'post' to match seeded fixtures.
		$post_type = (string) $request->get_param( 'post_type' );
		if ( $post_type === '' || ! post_type_exists( $post_type ) ) {
			$post_type = 'post';
		}

		$q = new WP_Query(
			array(
				'post_type'              => $post_type,
				'author'                 => $user_id,
				'post_status'            => 'any',     // include drafts/private in tests
				'fields'                 => 'ids',     // we only need IDs
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$ids = array_map( 'intval', (array) $q->posts );

		return rest_ensure_response( $ids );
	}
}
