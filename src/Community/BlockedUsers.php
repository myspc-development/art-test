<?php
namespace ArtPulse\Community;

use ArtPulse\DB\DbEnsure;

class BlockedUsers {

        public static function maybe_install_table(): void {
                global $wpdb;
                $table = $wpdb->prefix . 'ap_blocked_users';
                DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) );
        }

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_blocked_users';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            user_id BIGINT NOT NULL,
            blocked_user_id BIGINT NOT NULL,
            PRIMARY KEY (user_id, blocked_user_id),
            KEY blocked_user_id (blocked_user_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function add( int $user_id, int $blocked_id ): void {
		global $wpdb;
                $table = $wpdb->prefix . 'ap_blocked_users';

                if ( ! DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) ) ) {
                        return;
                }

                $wpdb->replace(
                        $table,
			array(
				'user_id'         => $user_id,
				'blocked_user_id' => $blocked_id,
			)
		);
	}

	public static function remove( int $user_id, int $blocked_id ): void {
		global $wpdb;
                $table = $wpdb->prefix . 'ap_blocked_users';

                if ( ! DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) ) ) {
                        return;
                }

                $wpdb->delete(
                        $table,
			array(
				'user_id'         => $user_id,
				'blocked_user_id' => $blocked_id,
			)
		);
	}

	public static function is_blocked( int $user_id, int $other_id ): bool {
		global $wpdb;
                $table = $wpdb->prefix . 'ap_blocked_users';

                if ( ! DbEnsure::table_exists_or_install( $table, array( self::class, 'install_table' ) ) ) {
                        return false;
                }

                return (bool) $wpdb->get_var(
                        $wpdb->prepare(
                                "SELECT 1 FROM $table WHERE user_id = %d AND blocked_user_id = %d",
                                $user_id,
                                $other_id
                        )
                );
	}
}
