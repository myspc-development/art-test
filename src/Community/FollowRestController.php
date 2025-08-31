<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use ArtPulse\Traits\Registerable;

class FollowRestController {

	use Registerable;

	private const HOOKS = array(
		'rest_api_init' => 'register_routes',
	);

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/follows',
			array(
				'methods'             => array( 'POST', 'DELETE', 'GET' ),
				'callback'            => array( self::class, 'handle_follows' ),
				'permission_callback' => fn() => is_user_logged_in(),
				'args'                => self::get_schema(),
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/followers/(?P<user_id>\\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_followers' ),
				'permission_callback' => fn() => is_user_logged_in(),
				'args'                => array(
					'user_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);
	}

	public static function get_schema(): array {
		return array(
			'post_id'   => array(
				'type'        => 'integer',
				'required'    => false,
				'description' => 'ID of the post to follow or unfollow.',
			),
			'post_type' => array(
				'type'        => 'string',
				'required'    => false,
				'enum'        => array( 'artpulse_artist', 'artpulse_event', 'artpulse_org', 'user' ),
				'description' => 'The post type being followed.',
			),
		);
	}

	public static function handle_follows( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		return match ( $request->get_method() ) {
			'POST'   => self::add_follow( $request ),
			'DELETE' => self::remove_follow( $request ),
			'GET'    => self::list_follows( $request ),
			default  => new WP_Error( 'invalid_method', 'Method not allowed', array( 'status' => 405 ) ),
		};
	}

	public static function add_follow( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Ensure database table exists in case installation routines missed it
		FollowManager::maybe_install_table();
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			error_log( '[FollowRestController] Missing table ' . $table );
			return new WP_Error( 'missing_table', 'Follows table not found.', array( 'status' => 500 ) );
		}
		$user_id   = get_current_user_id();
		$post_id   = absint( $request['post_id'] );
		$post_type = sanitize_key( $request['post_type'] );

		if ( $post_type === 'user' ) {
			if ( ! get_user_by( 'id', $post_id ) ) {
				return new WP_Error( 'invalid_post', 'User not found.', array( 'status' => 404 ) );
			}
		} elseif ( ! get_post( $post_id ) ) {
				return new WP_Error( 'invalid_post', 'Post not found.', array( 'status' => 404 ) );
		}

		$follows = get_user_meta( $user_id, '_ap_follows', true ) ?: array();
		if ( ! in_array( $post_id, $follows, true ) ) {
			$follows[] = $post_id;
			update_user_meta( $user_id, '_ap_follows', $follows );
		}

		FollowManager::add_follow( $user_id, $post_id, $post_type );
		if ( $wpdb->last_error ) {
			error_log( '[FollowRestController] DB error: ' . $wpdb->last_error );
			return new WP_Error( 'db_error', 'Error adding follow.', array( 'status' => 500 ) );
		}

		return \rest_ensure_response(
			array(
				'status'  => 'following',
				'follows' => $follows,
			)
		);
	}

	public static function remove_follow( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Ensure database table exists
		FollowManager::maybe_install_table();
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			error_log( '[FollowRestController] Missing table ' . $table );
			return new WP_Error( 'missing_table', 'Follows table not found.', array( 'status' => 500 ) );
		}
		$user_id   = get_current_user_id();
		$post_id   = absint( $request['post_id'] );
		$post_type = sanitize_key( $request['post_type'] );

		if ( $post_type === 'user' ) {
			if ( ! get_user_by( 'id', $post_id ) ) {
				return new WP_Error( 'invalid_post', 'User not found.', array( 'status' => 404 ) );
			}
		} elseif ( ! get_post( $post_id ) ) {
				return new WP_Error( 'invalid_post', 'Post not found.', array( 'status' => 404 ) );
		}

		$follows = get_user_meta( $user_id, '_ap_follows', true ) ?: array();
		if ( ( $key = array_search( $post_id, $follows ) ) !== false ) {
			unset( $follows[ $key ] );
			update_user_meta( $user_id, '_ap_follows', array_values( $follows ) );
		}

		FollowManager::remove_follow( $user_id, $post_id, $post_type );
		if ( $wpdb->last_error ) {
			error_log( '[FollowRestController] DB error: ' . $wpdb->last_error );
			return new WP_Error( 'db_error', 'Error removing follow.', array( 'status' => 500 ) );
		}

		return \rest_ensure_response(
			array(
				'status'  => 'unfollowed',
				'follows' => $follows,
			)
		);
	}

	public static function list_follows( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Ensure database table exists before querying
		FollowManager::maybe_install_table();
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			error_log( '[FollowRestController] Missing table ' . $table );
			return new WP_Error( 'missing_table', 'Follows table not found.', array( 'status' => 500 ) );
		}

		$user_id = get_current_user_id();
		$type    = $request['post_type'] ? sanitize_key( $request['post_type'] ) : null;

		$rows = FollowManager::get_user_follows( $user_id, $type );
		if ( $wpdb->last_error ) {
			error_log( '[FollowRestController] DB error: ' . $wpdb->last_error );
			return new WP_Error( 'db_error', 'Error retrieving follows.', array( 'status' => 500 ) );
		}
		return \rest_ensure_response( $rows );
	}

	public static function get_followers( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		// Ensure follows table exists
		FollowManager::maybe_install_table();
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			error_log( '[FollowRestController] Missing table ' . $table );
			return new WP_Error( 'missing_table', 'Follows table not found.', array( 'status' => 500 ) );
		}
		$user_id   = absint( $request['user_id'] );
		$followers = FollowManager::get_followers( $user_id );
		if ( $wpdb->last_error ) {
			error_log( '[FollowRestController] DB error: ' . $wpdb->last_error );
			return new WP_Error( 'db_error', 'Error retrieving followers.', array( 'status' => 500 ) );
		}
		return \rest_ensure_response(
			array(
				'user_id'   => $user_id,
				'followers' => $followers,
			)
		);
	}
}
