<?php
namespace ArtPulse\Core;

class EventMetrics {

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_metrics';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            metric VARCHAR(20) NOT NULL,
            day DATE NOT NULL,
            count BIGINT NOT NULL DEFAULT 0,
            UNIQUE KEY event_metric_day (event_id, metric, day)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_metrics';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function log_metric( int $event_id, string $metric, int $amount = 1 ): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_metrics';
		$day     = current_time( 'Y-m-d' );
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET count = count + %d WHERE event_id = %d AND metric = %s AND day = %s",
				$amount,
				$event_id,
				$metric,
				$day
			)
		);
		if ( ! $updated ) {
			$wpdb->insert(
				$table,
				array(
					'event_id' => $event_id,
					'metric'   => $metric,
					'day'      => $day,
					'count'    => $amount,
				),
				array( '%d', '%s', '%s', '%d' )
			);
		}
		do_action( 'ap_event_metric_logged', $event_id, $metric, $amount );
	}

	public static function get_counts( int $event_id, string $metric, int $days = 30 ): array {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_metrics';
		$since  = date( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
		$rows   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT day, count FROM {$table} WHERE event_id = %d AND metric = %s AND day >= %s ORDER BY day ASC",
				$event_id,
				$metric,
				$since
			)
		);
		$output = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$d            = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
			$output[ $d ] = 0;
		}
		foreach ( $rows as $row ) {
			$output[ $row->day ] = (int) $row->count;
		}
		return array(
			'days'   => array_keys( $output ),
			'counts' => array_values( $output ),
		);
	}
}
