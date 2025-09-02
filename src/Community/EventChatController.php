<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_Error;

class EventChatController {

	/**
	 * Ensure the event chat table exists.
	 */
	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_chat';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}
	/**
	 * Install the event chat table.
	 */
	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_chat';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY event_id (event_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql );
		}
		dbDelta( $sql );
	}

	/**
	 * Check if the current user can post to an event chat.
	 */
	public static function can_post( WP_REST_Request $req ): bool {
                $user_id  = \get_current_user_id();
                if ( ! $user_id ) {
                        return false;
                }
                $event_id = absint( $req['id'] );
                $list     = \get_post_meta( $event_id, 'event_rsvp_list', true );
                return is_array( $list ) && in_array( $user_id, $list, true );
	}
}
