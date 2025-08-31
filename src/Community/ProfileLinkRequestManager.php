<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ProfileLinkRequestManager {

	public static function register(): void {
		self::maybe_install_table();
	}

	public static function handle_create_request( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id   = get_current_user_id();
		$target_id = absint( $request['target_id'] );

		if ( ! get_post( $target_id ) ) {
			return new WP_Error( 'invalid_target', 'Target post not found.', array( 'status' => 404 ) );
		}

		$request_id = wp_insert_post(
			array(
				'post_type'   => 'ap_link_request',
				'post_status' => 'pending',
				'post_title'  => "Link Request: User {$user_id} to {$target_id}",
				'post_author' => $user_id,
				'meta_input'  => array( '_ap_target_id' => $target_id ),
			)
		);

		return \rest_ensure_response(
			array(
				'request_id' => $request_id,
				'status'     => 'pending',
			)
		);
	}

	// 🔧 This is the missing method you're calling on plugin activation
	public static function install_link_request_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'ap_link_requests_meta';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            target_id BIGINT(20) UNSIGNED NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	/**
	 * Ensure the link request table exists.
	 */
	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_link_requests_meta';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_link_request_table();
		}
	}

	/**
	 * Create a new profile link request post.
	 */
	public static function create( int $artist_user_id, int $org_id, string $message = '' ): int {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ap_profile_link_req',
				'post_status' => 'publish',
				'post_title'  => 'Profile Link Request',
				'post_author' => $artist_user_id,
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}

		update_post_meta( $post_id, 'artist_user_id', $artist_user_id );
		update_post_meta( $post_id, 'org_id', $org_id );
		update_post_meta( $post_id, 'message', $message );
		update_post_meta( $post_id, 'requested_on', current_time( 'mysql' ) );
		update_post_meta( $post_id, 'status', 'pending' );

		if ( class_exists( '\\ArtPulse\\Community\\NotificationManager' ) ) {
			$org_post = get_post( $org_id );
			$owner    = $org_post ? (int) $org_post->post_author : 0;
			if ( $owner ) {
				NotificationManager::add( $owner, 'link_request_sent', $post_id, $artist_user_id, $message );
			}
		}

		return (int) $post_id;
	}

	/**
	 * Approve a pending request and create a link post.
	 */
	public static function approve( int $request_id, int $user_id ): void {
		$request = get_post( $request_id );
		if ( ! $request || $request->post_type !== 'ap_profile_link_req' ) {
			return;
		}

		$artist = (int) get_post_meta( $request_id, 'artist_user_id', true );
		$org    = (int) get_post_meta( $request_id, 'org_id', true );

		$link_id = wp_insert_post(
			array(
				'post_type'   => 'ap_profile_link',
				'post_status' => 'publish',
				'post_title'  => 'Profile Link',
				'post_author' => $user_id,
			)
		);

		if ( ! is_wp_error( $link_id ) ) {
			update_post_meta( $link_id, 'artist_user_id', $artist );
			update_post_meta( $link_id, 'org_id', $org );
			update_post_meta( $link_id, 'request_id', $request_id );
			update_post_meta( $link_id, 'requested_on', get_post_meta( $request_id, 'requested_on', true ) );
			update_post_meta( $link_id, 'approved_on', current_time( 'mysql' ) );
			update_post_meta( $link_id, 'status', 'approved' );
		}

		update_post_meta( $request_id, 'status', 'approved' );
		update_post_meta( $request_id, 'approved_on', current_time( 'mysql' ) );
		update_post_meta( $request_id, 'approved_by', $user_id );

		if ( class_exists( '\\ArtPulse\\Community\\NotificationManager' ) && $artist ) {
			NotificationManager::add( $artist, 'link_request_approved', $link_id, $org );
		}
	}

	/**
	 * Deny a pending request.
	 */
	public static function deny( int $request_id, int $user_id ): void {
		$request = get_post( $request_id );
		if ( ! $request || $request->post_type !== 'ap_profile_link_req' ) {
			return;
		}

		update_post_meta( $request_id, 'status', 'denied' );
		update_post_meta( $request_id, 'denied_on', current_time( 'mysql' ) );
		update_post_meta( $request_id, 'denied_by', $user_id );

		$artist = (int) get_post_meta( $request_id, 'artist_user_id', true );
		if ( class_exists( '\\ArtPulse\\Community\\NotificationManager' ) && $artist ) {
			NotificationManager::add( $artist, 'link_request_denied', $request_id, $user_id );
		}
	}
}
