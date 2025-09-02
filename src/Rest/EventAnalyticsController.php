<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

/**
 * Provides simple event metrics for organization dashboards.
 */
class EventAnalyticsController extends WP_REST_Controller {
	use RestResponder;

	// Ensure tests hit the exact namespace they expect.
	protected $namespace = 'ap/v1';

	public static function register(): void {
		$controller = new self();
		add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
		add_action( 'ap_rsvp_changed', array( self::class, 'invalidate_cache' ), 10, 1 );
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/analytics/events/summary',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_summary' ),
				// Unauthenticated -> 401, authenticated but unauthorized -> 403
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
				'args'                => array(
					// Accept either range or explicit dates; clamp internally.
					'range' => array(
						'type'    => 'string',
						'default' => '30d',
					),
					'start' => array( 'type' => 'string' ),
					'end'   => array( 'type' => 'string' ),
				),
			)
		);
	}

	public function get_summary( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		$start   = $request->get_param( 'start' );
		$end     = $request->get_param( 'end' );
		$range   = $request->get_param( 'range' ) ?: '30d';

		$tz      = wp_timezone();
		$tz_name = wp_timezone_string() ?: 'UTC';
		$utc_tz  = new \DateTimeZone( 'UTC' );

		// Parse & clamp the date window.
		if ( $start && $end ) {
			$start_dt  = new \DateTime( $start, $tz );
			$end_dt    = new \DateTime( $end, $tz );
			$diff_days = $start_dt->diff( $end_dt )->days;
			if ( $start_dt > $end_dt || $diff_days > 365 ) {
				return new \WP_Error( 'invalid_range', __( 'Invalid range', 'artpulse' ), array( 'status' => 400 ) );
			}
		} else {
			$days     = intval( rtrim( $range, 'd' ) ) ?: 30;
			$days     = max( 1, min( 365, $days ) );
			$end_dt   = new \DateTime( 'now', $tz );
			$start_dt = ( clone $end_dt )->modify( '-' . $days . ' days' );
		}

		$after_local = $start_dt->format( 'Y-m-d' );
		$until_local = $end_dt->format( 'Y-m-d' );
		$after_utc   = ( clone $start_dt )->setTimezone( $utc_tz )->format( 'Y-m-d' );
		$until_utc   = ( clone $end_dt )->setTimezone( $utc_tz )->format( 'Y-m-d' );

		$cache_key = self::cache_key( $user_id, $start, $end, $range );
		if ( false !== ( $cached = get_transient( $cache_key ) ) ) {
			return \rest_ensure_response( $cached );
		}

		// Build zero-filled series for each local day in range.
		$series = array();
		$cursor = ( clone $start_dt );
		while ( $cursor <= $end_dt ) {
			$series[ $cursor->format( 'Y-m-d' ) ] = 0;
			$cursor->modify( '+1 day' );
		}

		// Query authored events in the local window (date_query uses local site time).
		$q   = new \WP_Query(
			array(
				'post_type'     => 'artpulse_event',
				'author'        => $user_id,
				'date_query'    => array(
					array(
						'after'     => $after_local,
						'inclusive' => true,
					),
				),
				'post_status'   => array( 'publish', 'draft', 'pending', 'future' ),
				'fields'        => 'ids',
				'nopaging'      => true,
				'no_found_rows' => true,
			)
		);
		$ids = $q->posts;

		$counts = array(
			'total'     => 0,
			'published' => 0,
			'draft'     => 0,
		);
		foreach ( $ids as $id ) {
			++$counts['total'];
			$st = get_post_status( $id );
			if ( $st === 'publish' ) {
				++$counts['published'];
			} elseif ( $st === 'draft' ) {
				++$counts['draft'];
			}
		}

		global $wpdb;
		$rsvps       = array(
			'going'     => 0,
			'waitlist'  => 0,
			'cancelled' => 0,
		);
		$unique      = 0;
		$total_rsvps = 0;
		$top_events  = array();
		$top_event   = '';

                if ( $ids ) {
                        $table  = $wpdb->prefix . 'ap_rsvps';
                        $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
                        if ( ! $exists ) {
                                return $this->fail( 'ap_db_missing', 'Required table missing', 500 );
                        }
                        $in    = implode( ',', array_map( 'intval', $ids ) );

			// Totals by status (robust to unknown statuses).
			$rows = $wpdb->get_results( "SELECT status, COUNT(*) c FROM {$table} WHERE event_id IN ($in) GROUP BY status", ARRAY_A ) ?: array();
			foreach ( $rows as $row ) {
				$st = (string) ( $row['status'] ?? '' );
				$c  = (int) ( $row['c'] ?? 0 );
				if ( isset( $rsvps[ $st ] ) ) {
					$rsvps[ $st ] = $c;
				}
				$total_rsvps += $c;
			}

			// Unique attendees (by email).
			$unique = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$table} WHERE event_id IN ($in)" ) ?: 0;

			// Trend: group by UTC date in SQL, then map buckets into local TZ in PHP (portable; no CONVERT_TZ).
			try {
				$rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DATE(created_at) d, COUNT(*) c
                         FROM {$table}
                         WHERE event_id IN ($in)
                           AND created_at BETWEEN %s AND %s
                         GROUP BY d
                         ORDER BY d",
						$after_utc . ' 00:00:00',
						$until_utc . ' 23:59:59'
					),
					ARRAY_A
				) ?: array();

				foreach ( $rows as $r ) {
					$utcDay = (string) ( $r['d'] ?? '' );
					$cnt    = (int) ( $r['c'] ?? 0 );
					if ( ! $utcDay ) {
						continue; }
					// Convert this UTC bucket to local "day" bucket edge.
					$dt = new \DateTime( $utcDay . ' 00:00:00', $utc_tz );
					$dt->setTimezone( $tz );
					$localKey = $dt->format( 'Y-m-d' );
					if ( array_key_exists( $localKey, $series ) ) {
						$series[ $localKey ] += $cnt;
					}
				}

				// Top events
				$tops       = $wpdb->get_results(
					"SELECT event_id, COUNT(*) c
                     FROM {$table}
                     WHERE event_id IN ($in)
                     GROUP BY event_id
                     ORDER BY c DESC
                     LIMIT 5",
					ARRAY_A
				) ?: array();
				$top_events = array_map(
					fn( $r ) => array(
						'title' => get_the_title( (int) ( $r['event_id'] ?? 0 ) ),
						'count' => (int) ( $r['c'] ?? 0 ),
					),
					$tops
				);
				$top_event  = $top_events[0]['title'] ?? '';
			} catch ( \Throwable $e ) {
				// Safe fallback: keep zeroed series and empty tops on DB quirks.
				$top_events = array();
				$top_event  = '';
			}
		}

		$confirmed_percent = $total_rsvps ? round( ( $rsvps['going'] / $total_rsvps ) * 100, 2 ) : 0;

		// Shape expected by tests + extra tiles/series for UI.
		$series_out = array();
		foreach ( $series as $d => $c ) {
			$series_out[] = array(
				'date'  => $d,
				'count' => (int) $c,
			);
		}

		$data = array(
			'timezone'          => $tz_name,
			'series'            => $series_out,                         // daily buckets
			'tiles'             => array(
				'total_events' => $counts['total'], // minimal tiles
				'total_rsvps'  => $total_rsvps,
			),
			'events'            => $counts,
			'rsvps'             => $rsvps,
			'total_rsvps'       => $total_rsvps,
			'unique_attendees'  => $unique,
			'confirmed_percent' => $confirmed_percent,
			// keep legacy keys for any UI already reading them
			'trend'             => $series_out,
			'top_event'         => $top_event,
			'top_events'        => $top_events,
		);

		set_transient( $cache_key, $data, MINUTE_IN_SECONDS );
		return \rest_ensure_response( $data );
	}

	protected static function cache_key( int $user_id, ?string $start, ?string $end, string $range ): string {
		$key = ( $start && $end ) ? ( $start . ':' . $end ) : $range;
		return 'ap_evt_summary_' . $user_id . '_' . md5( $key );
	}

	public static function invalidate_cache( int $event_id ): void {
		$user_id = (int) get_post_field( 'post_author', $event_id );
		foreach ( array( '7d', '30d', '90d' ) as $r ) {
			delete_transient( self::cache_key( $user_id, null, null, $r ) );
		}
	}
}
