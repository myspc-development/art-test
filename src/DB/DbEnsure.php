<?php
namespace ArtPulse\DB;

use wpdb;

/**
 * Utility to ensure custom tables exist before querying.
 */
class DbEnsure {
	/**
	 * Ensure a custom table exists.
	 *
	 * Attempts to create the table using the provided installer if it is
	 * missing. Returns true when the table exists after this call.
	 *
	 * @param string   $table     Fully-qualified table name (with prefix).
	 * @param callable $installer Callback that installs the table.
	 * @return bool Whether the table exists after attempting installation.
	 */
	public static function table_exists_or_install( string $table, callable $installer ): bool {
		global $wpdb; /** @var wpdb $wpdb */

		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists === $table ) {
			return true;
		}

		$installer();

		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		return $exists === $table;
	}
}
