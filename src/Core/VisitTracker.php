<?php
namespace ArtPulse\Core;

class VisitTracker {

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_checkins';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            institution VARCHAR(255) NOT NULL DEFAULT '',
            group_size INT NOT NULL DEFAULT 1,
            visit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY event_id (event_id),
            KEY user_id (user_id),
            KEY visit_date (visit_date)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_checkins';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function record( int $event_id, int $user_id = 0, string $institution = '', int $group_size = 1 ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_checkins';
		$wpdb->insert(
			$table,
			array(
				'event_id'    => $event_id,
				'user_id'     => $user_id,
				'institution' => $institution,
				'group_size'  => $group_size,
				'visit_date'  => current_time( 'mysql' ),
			)
		);
	}

	public static function get_visits( int $event_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_checkins';
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d ORDER BY visit_date DESC", $event_id ),
			ARRAY_A
		);
	}
}
