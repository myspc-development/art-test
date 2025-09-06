<?php
namespace ArtPulse\Community;

use ArtPulse\Core\UserEngagementLogger;

class FollowManager {
	/**
	 * Create the follows table if not exists.
	 */
	public static function install_follows_table() {
		global $wpdb;
		$table           = $wpdb->prefix . 'ap_follows';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            object_id BIGINT NOT NULL,
            object_type VARCHAR(32) NOT NULL,
            followed_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_object (user_id, object_id, object_type)
        ) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	/**
	 * Add a follow record and trigger notification.
	 */
	public static function add_follow( $user_id, $object_id, $object_type ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		$wpdb->replace(
			$table,
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'followed_on' => current_time( 'mysql' ),
			)
		);

		UserEngagementLogger::log( $user_id, 'follow', $object_id );
		\ArtPulse\Personalization\RecommendationEngine::log( $user_id, $object_type, $object_id, 'follow' );

		// --- Maintain following/follower counts ---
		$following = (int) get_user_meta( $user_id, 'ap_following_count', true );
		update_user_meta( $user_id, 'ap_following_count', $following + 1 );

		if ( $object_type === 'user' ) {
			$followers = (int) get_user_meta( $object_id, 'ap_follower_count', true );
			update_user_meta( $object_id, 'ap_follower_count', $followers + 1 );
		}

		/**
		 * Action triggered after a follow is added.
		 */
		do_action( 'ap_follow_added', $user_id, $object_id, $object_type );

		// --- Notify the owner (if not following self) ---
		if ( class_exists( '\ArtPulse\Community\NotificationManager' ) ) {
			if ( $object_type === 'user' ) {
				if ( $object_id !== $user_id ) {
					\ArtPulse\Community\NotificationManager::add(
						$object_id,
						'follower',
						$object_id,
						$user_id,
						'You have a new follower.'
					);
				}
			} else {
				$owner_id = self::get_owner_user_id( $object_id, $object_type );
				if ( $owner_id && $owner_id !== $user_id ) {
					$title = self::get_object_title( $object_id, $object_type );
					\ArtPulse\Community\NotificationManager::add(
						$owner_id,
						'follower',
						$object_id,
						$user_id,
						sprintf( esc_html__( 'You have a new follower on your %1$s "%2$s".', 'artpulse' ), esc_html( $object_type ), esc_html( $title ) )
					);
				}
			}
		}
	}

	/**
	 * Remove a follow.
	 */
	public static function remove_follow( $user_id, $object_id, $object_type ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_follows';
		$deleted = $wpdb->delete(
			$table,
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			)
		);

		if ( $deleted ) {
			$following = max( 0, (int) get_user_meta( $user_id, 'ap_following_count', true ) - 1 );
			update_user_meta( $user_id, 'ap_following_count', $following );

			if ( $object_type === 'user' ) {
				$followers = max( 0, (int) get_user_meta( $object_id, 'ap_follower_count', true ) - 1 );
				update_user_meta( $object_id, 'ap_follower_count', $followers );
			}
		}

		/**
		 * Action triggered after a follow is removed.
		 */
		do_action( 'ap_follow_removed', $user_id, $object_id, $object_type );
	}

	/**
	 * Is the user following this object?
	 */
	public static function is_following( $user_id, $object_id, $object_type ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE user_id = %d AND object_id = %d AND object_type = %s",
				$user_id,
				$object_id,
				$object_type
			)
		);
	}

	/**
	 * Get all follows for a user (optionally filtered by type).
	 * Returns array of objects: object_id, object_type, followed_on.
	 */
	public static function get_user_follows( $user_id, $object_type = null ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$sql    = "SELECT object_id, object_type, followed_on FROM $table WHERE user_id = %d";
		$params = array( $user_id );
		if ( $object_type ) {
			$sql     .= ' AND object_type = %s';
			$params[] = $object_type;
		}
		$sql .= ' ORDER BY followed_on DESC';
		return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
	}

	/**
	 * Get followers of a user.
	 *
	 * @param int $user_id
	 * @return array<int>
	 */
	public static function get_followers( int $user_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		$rows  = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT user_id FROM $table WHERE object_id = %d AND object_type = %s ORDER BY followed_on DESC",
				$user_id,
				'user'
			)
		);
		return array_map( 'intval', $rows );
	}

	/**
	 * Helper: Get the owner user ID of an object.
	 */
	private static function get_owner_user_id( $object_id, $object_type ) {
		if ( $object_type === 'user' ) {
			return (int) $object_id;
		}

		// For all post types, the post_author is the owner
		if ( post_type_exists( $object_type ) ) {
			$post = get_post( $object_id );
			return $post ? (int) $post->post_author : 0;
		}
		return 0;
	}

	/**
	 * Helper: Get object title (post title).
	 */
	private static function get_object_title( $object_id, $object_type ) {
		if ( post_type_exists( $object_type ) ) {
			$post = get_post( $object_id );
			return $post ? $post->post_title : '';
		}
		return '';
	}

	/**
	 * Ensure the follows table exists.
	 */
	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_follows';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_follows_table();
		}
	}
}
