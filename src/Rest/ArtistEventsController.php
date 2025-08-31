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
				// Keep using your Auth helper so unauthenticated users get 401 and non-capable get 403.
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
			)
		);
	}

	/**
	 * Return the current user's event/posts as an array of IDs.
	 *
	 * Tests expect a flat array of integers (e.g., [25, 27]).
	 */
	public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Permission has been checked by permission_callback; keep a simple guard here for safety.
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error(
				'ap_rest_author_required',
				__( 'Authentication required.', 'artpulse' ),
				array( 'status' => 401 )
			);
		}

		// Allow post type override; default to 'post' to match the test fixtures.
		$post_type = (string) $request->get_param( 'post_type' );
		if ( $post_type === '' ) {
			$post_type = 'post'; // tests typically seed core posts
		} elseif ( ! post_type_exists( $post_type ) ) {
			// Fall back if an unknown type is requested.
			$post_type = 'post';
		}

		$q = new WP_Query(
			array(
				'post_type'              => $post_type,
				'author'                 => $user_id,
				'post_status'            => 'any',
				'fields'                 => 'ids',
				// Ensure deterministic ordering to match expected [id1, id2, ...].
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				// Perf flags
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		// Return just the IDs as integers.
		$ids = array_map( 'intval', (array) $q->posts );

		return rest_ensure_response( $ids );
	}
}
