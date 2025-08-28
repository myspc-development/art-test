<?php
namespace ArtPulse\Install;

use wpdb;
class Schema {
	public static function ensure(): void {
		global $wpdb; /** @var wpdb $wpdb */
		$table   = $wpdb->prefix . 'ap_donations';
		$collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = "CREATE TABLE {$table} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      org_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
      user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
      amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      donated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
      PRIMARY KEY (id),
      KEY org_id (org_id),
      KEY user_id (user_id),
      KEY donated_at (donated_at)
    ) {$collate};";
		\dbDelta( $sql );
	}
	public static function column_exists( string $table, string $col ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table} LIKE %s",
				$col
			)
		);
	}
}
