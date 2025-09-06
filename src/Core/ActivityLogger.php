<?php
namespace ArtPulse\Core;

use ArtPulse\DB\DbEnsure;

class ActivityLogger {

	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'maybe_install_table' ) );
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_activity_logs';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            org_id BIGINT NULL,
            user_id BIGINT NULL,
            action_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL DEFAULT '',
            metadata LONGTEXT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY org_id (org_id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY logged_at (logged_at)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function maybe_install_table(): void {
			global $wpdb;
			$table = $wpdb->prefix . 'ap_activity_logs';
			DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) );
	}

	public static function log( ?int $org_id, ?int $user_id, string $action_type, string $description, array $metadata = array() ): void {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return;
		}
				$table = $wpdb->prefix . 'ap_activity_logs';

		if ( ! DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) ) ) {
				return;
		}

				$wpdb->insert(
					$table,
					array(
						'org_id'      => $org_id,
						'user_id'     => $user_id,
						'action_type' => $action_type,
						'description' => $description,
						'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
						'metadata'    => wp_json_encode( $metadata ),
						'logged_at'   => current_time( 'mysql' ),
					)
				);
	}

	/**
	 * Fetch recent activity log entries.
	 */
	public static function get_logs( ?int $org_id, ?int $user_id, int $limit = 25 ): array {
		global $wpdb;
				$table = $wpdb->prefix . 'ap_activity_logs';

		if ( ! DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) ) ) {
				return array();
		}

		$where = array();
		$args  = array();

		if ( $org_id !== null ) {
			$where[] = 'org_id = %d';
			$args[]  = $org_id;
		}

		if ( $user_id !== null ) {
			$where[] = 'user_id = %d';
			$args[]  = $user_id;
		}

		if ( ! $where ) {
			return array();
		}

		$args[] = $limit;
		$sql    = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' OR ', $where ) . ' ORDER BY logged_at DESC LIMIT %d';

		return $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) );
	}
}
