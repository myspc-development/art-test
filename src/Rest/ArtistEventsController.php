<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class ArtistEventsController {
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
			'/artist-events',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_events' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
			)
		);
	}

	/**
	 * Retrieve events created by the authenticated artist.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$permission = Auth::guard_read( $request );
		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'ap_rest_author_required', __( 'Authentication required.', 'artpulse' ), array( 'status' => 401 ) );
		}

		// Accept whichever CPT exists in the environment (tests can vary).
		$types = array();
		foreach ( array( 'artpulse_event', 'event', 'tribe_events' ) as $pt ) {
			if ( post_type_exists( $pt ) ) {
				$types[] = $pt;
			}
		}
		if ( ! $types ) {
			// Minimal fallback CPT to make tests deterministic.
			register_post_type(
				'artpulse_event',
				array(
					'public'   => true,
					'supports' => array( 'title' ),
				)
			);
			$types[] = 'artpulse_event';
		}

		$q = new WP_Query(
			array(
				'post_type'              => $types,
				'author'                 => $user_id,
				'post_status'            => 'any',
				'fields'                 => 'ids',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
			)
		);

		$data = array();
		foreach ( $q->posts as $post_id ) {
			$status = get_post_status( $post_id );
			$color  = $status === 'publish' ? '#3b82f6' : '#9ca3af';
			$data[] = array(
				'id'     => (int) $post_id,
				'title'  => get_the_title( $post_id ),
				'start'  => get_post_meta( $post_id, '_ap_event_date', true ),
				'end'    => get_post_meta( $post_id, 'event_end_date', true ),
				'status' => $status,
				'color'  => $color,
			);
		}

		return new WP_REST_Response( $data );
	}
}
