<?php
namespace ArtPulse\Core;

class CompetitionEntryManager {

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_competition_entries';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            competition_id BIGINT NOT NULL,
            artwork_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            votes INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY comp_artwork (competition_id, artwork_id),
            KEY competition_id (competition_id),
            KEY artwork_id (artwork_id),
            KEY user_id (user_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_competition_entries';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function add_entry( int $competition_id, int $artwork_id, int $user_id ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_competition_entries';
		$wpdb->replace(
			$table,
			array(
				'competition_id' => $competition_id,
				'artwork_id'     => $artwork_id,
				'user_id'        => $user_id,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s' )
		);
	}

	public static function vote( int $entry_id, int $user_id ): int {
		$voted = get_user_meta( $user_id, 'ap_comp_votes', true );
		if ( ! is_array( $voted ) ) {
			$voted = array();
		}
		if ( in_array( $entry_id, $voted, true ) ) {
			return self::get_votes( $entry_id );
		}
		$voted[] = $entry_id;
		update_user_meta( $user_id, 'ap_comp_votes', $voted );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_competition_entries';
		$wpdb->query( $wpdb->prepare( "UPDATE $table SET votes = votes + 1 WHERE id = %d", $entry_id ) );

		return self::get_votes( $entry_id );
	}

	public static function get_votes( int $entry_id ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_competition_entries';
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT votes FROM $table WHERE id = %d", $entry_id ) );
	}

	public static function get_entries( int $competition_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_competition_entries';
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE competition_id = %d ORDER BY votes DESC", $competition_id ),
			ARRAY_A
		);
	}
}
