<?php
namespace ArtPulse\Discovery;

use ArtPulse\Core\EventMetrics;
use ArtPulse\Monetization\EventBoostManager;

class EventRankingManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'init', array( self::class, 'schedule_cron' ) );
		add_action( 'ap_event_ranking_calculate', array( self::class, 'calculate_scores' ) );
		add_filter( 'the_content', array( self::class, 'maybe_debug_score' ) );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_event_rankings';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_event_rankings';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            event_id BIGINT NOT NULL,
            score FLOAT NOT NULL DEFAULT 0,
            calculated_at DATETIME NOT NULL,
            PRIMARY KEY (event_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'ap_event_ranking_calculate' ) ) {
			wp_schedule_event( time(), 'hourly', 'ap_event_ranking_calculate' );
		}
	}

	private static function metric_sum( int $event_id, string $metric, int $days ): int {
		$data = EventMetrics::get_counts( $event_id, $metric, $days );
		return array_sum( $data['counts'] );
	}

	public static function calculate_scores(): void {
		$events = get_posts(
			array(
				'post_type'      => 'artpulse_event',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_rankings';

		foreach ( $events as $event_id ) {
			$views7d      = self::metric_sum( $event_id, 'view', 7 );
			$saves        = self::metric_sum( $event_id, 'favorite', 7 );
			$verified     = get_post_meta( $event_id, '_ap_verified_host', true ) ? 1 : 0;
			$artist_score = (float) get_post_meta( $event_id, '_ap_artist_score', true );
			$boost_bonus  = EventBoostManager::is_boosted( $event_id ) ? 25 : 0;
			$score        = 0.35 * log1p( $views7d )
					+ 0.25 * log1p( $saves )
					+ 0.20 * $verified
					+ 0.10 * $artist_score
					+ $boost_bonus;
			$score        = max( 0, min( 100, $score * 10 ) );

			$wpdb->replace(
				$table,
				array(
					'event_id'      => $event_id,
					'score'         => $score,
					'calculated_at' => current_time( 'mysql' ),
				)
			);
		}
	}

	public static function get_score( int $event_id ): float {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_rankings';
		$val   = $wpdb->get_var( $wpdb->prepare( "SELECT score FROM $table WHERE event_id = %d", $event_id ) );
		return $val ? (float) $val : 0.0;
	}

	public static function maybe_debug_score( string $content ): string {
		if ( is_singular( 'artpulse_event' ) && isset( $_GET['show_score'] ) && $_GET['show_score'] == '1' ) {
			global $post;
			$score = self::get_score( $post->ID );
						return '<p>' . sprintf( esc_html__( 'Score: %1$s', 'artpulse' ), esc_html( $score ) ) . '</p>' . $content;
		}
				return $content;
	}
}
