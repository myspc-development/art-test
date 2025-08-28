<?php
namespace ArtPulse\Community;

class CommentReports {

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_comment_reports';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_comment_reports';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            comment_id BIGINT NOT NULL,
            reporter_id BIGINT NOT NULL,
            reason TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY comment_id (comment_id),
            KEY reporter_id (reporter_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function add_report( int $comment_id, int $user_id, string $reason = '' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_comment_reports';
		$wpdb->insert(
			$table,
			array(
				'comment_id'  => $comment_id,
				'reporter_id' => $user_id,
				'reason'      => $reason,
			)
		);
	}

	public static function count_reports( int $comment_id ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_comment_reports';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE comment_id = %d", $comment_id ) );
	}

	public static function clear_reports( int $comment_id ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_comment_reports';
		$wpdb->delete( $table, array( 'comment_id' => $comment_id ) );
	}
}
