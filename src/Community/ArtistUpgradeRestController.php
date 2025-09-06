<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use ArtPulse\Core\Plugin;
use ArtPulse\Traits\Registerable;

class ArtistUpgradeRestController {

	use Registerable;

	private const HOOKS = array(
		'rest_api_init' => 'register_routes',
	);

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artist-upgrade' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/artist-upgrade',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'handle_request' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/upgrade-to-artist' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/upgrade-to-artist',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'upgrade_to_artist' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}
	}

	public static function handle_request( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'Must be logged in', array( 'status' => 401 ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user || user_can( $user, 'artist' ) ) {
			return new WP_Error( 'already_artist', 'Already an artist', array( 'status' => 400 ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ap_artist_request',
				'post_status' => 'pending',
				'post_title'  => 'Artist Upgrade: User ' . $user_id,
				'post_author' => $user_id,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return \rest_ensure_response(
			array(
				'request_id' => $post_id,
				'status'     => 'pending',
			)
		);
	}

	/**
	 * Directly upgrades the current user to an artist and creates a profile post.
	 */
	public static function upgrade_to_artist( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'Must be logged in', array( 'status' => 401 ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', 'Invalid user', array( 'status' => 400 ) );
		}

		if ( ! in_array( 'artist', $user->roles, true ) ) {
			$user->add_role( 'artist' );
		}

		$profile_id = (int) get_user_meta( $user_id, 'ap_artist_profile_id', true );
		if ( ! $profile_id || 'artist_profile' !== get_post_type( $profile_id ) ) {
			$profile_id = wp_insert_post(
				array(
					'post_type'   => 'artist_profile',
					'post_status' => 'publish',
					'post_title'  => $user->display_name ?: 'Artist ' . $user_id,
					'post_author' => $user_id,
				),
				true
			);

			if ( is_wp_error( $profile_id ) ) {
				return $profile_id;
			}

			update_user_meta( $user_id, 'ap_artist_profile_id', $profile_id );
		}

				$url = add_query_arg( 'onboarding', '1', Plugin::get_artist_dashboard_url() );

		return \rest_ensure_response(
			array(
				'profile_id'     => $profile_id,
				'onboarding_url' => $url,
			)
		);
	}
}
