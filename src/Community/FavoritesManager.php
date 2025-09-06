<?php
namespace ArtPulse\Community;

use ArtPulse\Community\NotificationManager;


class FavoritesManager {
	public static function add_favorite( $user_id, $object_id, $object_type, $notify_user = false ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_favorites';
		$wpdb->replace(
			$table,
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'created_at'  => current_time( 'mysql' ),
			)
		);
		\ArtPulse\Core\UserEngagementLogger::log( $user_id, 'favorite', $object_id );
		\ArtPulse\Personalization\RecommendationEngine::log( $user_id, $object_type, $object_id, 'favorite' );

		// --- Notify owner (if not self) ---
		$owner_id = self::get_owner_user_id( $object_id, $object_type );
		if ( $owner_id && $owner_id !== $user_id ) {
			$title = self::get_object_title( $object_id, $object_type );
			NotificationManager::add(
				$owner_id,
				'favorite',
				$object_id,
				$user_id,
				sprintf( esc_html__( 'Your %1$s "%2$s" was favorited!', 'artpulse' ), esc_html( $object_type ), esc_html( $title ) )
			);
		}

		// --- Optionally notify the user that they favorited something ---
		if ( $notify_user ) {
			$title = self::get_object_title( $object_id, $object_type );
			NotificationManager::add(
				$user_id,
				'favorite_added',
				$object_id,
				$owner_id,
				sprintf( esc_html__( 'You favorited the %1$s "%2$s".', 'artpulse' ), esc_html( $object_type ), esc_html( $title ) )
			);
		}

		do_action( 'ap_favorite_added', $user_id, $object_id, $object_type );
	}

	public static function remove_favorite( $user_id, $object_id, $object_type ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_favorites';
		$wpdb->delete(
			$table,
			array(
				'user_id'     => $user_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
			)
		);
		// No notification on unfavorite (usually)

		do_action( 'ap_favorite_removed', $user_id, $object_id, $object_type );
	}

	public static function is_favorited( $user_id, $object_id, $object_type ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_favorites';
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE user_id = %d AND object_id = %d AND object_type = %s",
				$user_id,
				$object_id,
				$object_type
			)
		);
	}

	public static function get_user_favorites( $user_id, $object_type = null ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_favorites';
		$sql    = "SELECT object_id, object_type, created_at FROM $table WHERE user_id = %d";
		$params = array( $user_id );
		if ( $object_type ) {
			$sql     .= ' AND object_type = %s';
			$params[] = $object_type;
		}
		$sql .= ' ORDER BY created_at DESC';
		return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
	}

	/**
	 * Get all favorites for a user grouped by simplified type names.
	 */
	public static function get_favorites( int $user_id ): array {
		$favorites = array(
			'event'             => array(),
			'artist'            => array(),
			'organization'      => array(),
			'artwork'           => array(),
			'forum'             => array(),
			'competition_entry' => array(),
		);

		foreach ( self::get_user_favorites( $user_id ) as $fav ) {
			$type = match ( $fav->object_type ) {
				'artpulse_event'   => 'event',
				'artpulse_artist'  => 'artist',
				'artpulse_org'     => 'organization',
				'artpulse_artwork' => 'artwork',
				'ap_forum_thread'  => 'forum',
				'ap_competition_entry' => 'competition_entry',
				default            => null,
			};

			if ( $type ) {
				$favorites[ $type ][] = (int) $fav->object_id;
			}
		}

		return $favorites;
	}

	// 🔽🔽 Helper to get the owner of an object (post author, etc)
	private static function get_owner_user_id( $object_id, $object_type ) {
		// You may want to map object_type to post types, etc.
		if ( in_array( $object_type, array( 'artpulse_artwork', 'artpulse_event', 'artpulse_artist', 'artpulse_org', 'ap_forum_thread' ) ) ) {
			$post = get_post( $object_id );
			return $post ? (int) $post->post_author : 0;
		}

		if ( $object_type === 'ap_competition_entry' ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ap_competition_entries';
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $table WHERE id = %d", $object_id ) );
		}

		return 0;
	}

	// 🔽🔽 Helper to get the title of the favorited object
	private static function get_object_title( $object_id, $object_type ) {
		if ( in_array( $object_type, array( 'artpulse_artwork', 'artpulse_event', 'artpulse_artist', 'artpulse_org', 'ap_forum_thread' ) ) ) {
			$post = get_post( $object_id );
			return $post ? $post->post_title : '';
		}

		if ( $object_type === 'ap_competition_entry' ) {
			global $wpdb;
			$table  = $wpdb->prefix . 'ap_competition_entries';
			$art_id = $wpdb->get_var( $wpdb->prepare( "SELECT artwork_id FROM $table WHERE id = %d", $object_id ) );
			$post   = $art_id ? get_post( $art_id ) : null;
			return $post ? $post->post_title : '';
		}

		return '';
	}

	/**
	 * Installer: create the favorites table
	 */
	public static function install_favorites_table() {
		global $wpdb;
		$table           = $wpdb->prefix . 'ap_favorites';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            object_id BIGINT NOT NULL,
            object_type VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_object (user_id, object_id, object_type)
        ) $charset_collate;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	/**
	 * Ensure the favorites table exists.
	 */
	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_favorites';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_favorites_table();
		}
	}
}
