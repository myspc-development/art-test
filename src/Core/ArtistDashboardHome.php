<?php
namespace ArtPulse\Core;

use WP_Query;

class ArtistDashboardHome {

	public static function get_artist_event_count( int $artist_id ): int {
		$q = new WP_Query(
			array(
				'post_type'      => 'artpulse_event',
				'post_status'    => 'publish',
				'author'         => $artist_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		return $q->post_count;
	}

	public static function get_artist_upcoming_event_count( int $artist_id ): int {
		$today = date( 'Y-m-d' );
		$q     = new WP_Query(
			array(
				'post_type'      => 'artpulse_event',
				'post_status'    => 'publish',
				'author'         => $artist_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'event_start_date',
						'value'   => $today,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);
		return $q->post_count;
	}

	public static function get_artist_total_rsvps( int $artist_id ): int {
		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'author'      => $artist_id,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		$total  = 0;
		foreach ( $events as $eid ) {
			$list   = get_post_meta( $eid, 'event_rsvp_list', true );
			$total += is_array( $list ) ? count( $list ) : 0;
		}
		return $total;
	}

	public static function get_artist_total_favorites( int $artist_id ): int {
		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'author'      => $artist_id,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		$total  = 0;
		foreach ( $events as $eid ) {
			$total += intval( get_post_meta( $eid, 'ap_favorite_count', true ) );
		}
		return $total;
	}

	public static function get_artist_recent_activity( int $artist_id, int $limit = 5 ): array {
		global $wpdb;
		$table    = $wpdb->prefix . 'ap_user_engagement_log';
		$query    = $wpdb->prepare(
			"SELECT l.type, l.logged_at, p.post_title FROM {$table} l JOIN {$wpdb->posts} p ON l.event_id = p.ID WHERE p.post_author = %d ORDER BY l.logged_at DESC LIMIT %d",
			$artist_id,
			$limit
		);
		$rows     = $wpdb->get_results( $query );
		$activity = array();
		foreach ( $rows as $row ) {
			$activity[] = array(
				'icon'    => $row->type === 'favorite' ? '❤' : '📅',
                                'message' => $row->type === 'favorite'
                                        ? sprintf( esc_html__( 'New favorite on "%1$s"', 'artpulse' ), esc_html( $row->post_title ) )
                                        : sprintf( esc_html__( 'New RSVP for "%1$s"', 'artpulse' ), esc_html( $row->post_title ) ),
				'date'    => mysql2date( get_option( 'date_format' ), $row->logged_at ),
			);
		}
		return $activity;
	}
}
