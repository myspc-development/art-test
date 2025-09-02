<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\EventMetrics;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class AnalyticsRestController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/trends' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/analytics/trends',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_trends' ),
                                       'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id' => array(
							'type'     => 'integer',
							'required' => true,
						),
						'days'     => array(
							'type'    => 'integer',
							'default' => 30,
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/export' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/analytics/export',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'export_csv' ),
                                       'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id' => array(
							'type'     => 'integer',
							'required' => true,
						),
						'days'     => array(
							'type'    => 'integer',
							'default' => 30,
						),
					),
				)
			);
		}
	}

	public static function get_trends( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request['event_id'] );
		$days     = max( 1, absint( $request['days'] ) );
		if ( ! $event_id ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

                global $wpdb;
                $table  = $wpdb->prefix . 'ap_tickets';
                $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
                if ( ! $exists ) {
                        return ( new self() )->fail( 'ap_db_missing', 'Required table missing', 500 );
                }

                $views   = EventMetrics::get_counts( $event_id, 'view', $days );
                $favs    = EventMetrics::get_counts( $event_id, 'favorite', $days );
                $tickets = self::get_ticket_counts( $event_id, $days );

		return \rest_ensure_response(
			array(
				'days'      => $views['days'],
				'views'     => $views['counts'],
				'favorites' => $favs['counts'],
				'tickets'   => $tickets['counts'],
			)
		);
	}

	private static function get_ticket_counts( int $event_id, int $days ): array {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_tickets';
		$since  = date( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
		$rows   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(purchase_date) AS day, COUNT(id) AS c FROM $table WHERE event_id = %d AND status = 'active' AND purchase_date >= %s GROUP BY day",
				$event_id,
				$since
			)
		);
		$output = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$d            = date( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
			$output[ $d ] = 0;
		}
		foreach ( $rows as $row ) {
			$output[ $row->day ] = (int) $row->c;
		}
		return array(
			'days'   => array_keys( $output ),
			'counts' => array_values( $output ),
		);
	}

	public static function export_csv( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$data = self::get_trends( $request );
		if ( $data instanceof WP_Error ) {
			return $data;
		}
		$data   = $data->get_data();
		$stream = fopen( 'php://temp', 'w' );
		fputcsv( $stream, array( 'day', 'views', 'favorites', 'tickets' ) );
		foreach ( $data['days'] as $i => $day ) {
			fputcsv( $stream, array( $day, $data['views'][ $i ], $data['favorites'][ $i ], $data['tickets'][ $i ] ) );
		}
		rewind( $stream );
		$csv = stream_get_contents( $stream );
		fclose( $stream );
		return new WP_REST_Response(
			$csv,
			200,
			array(
				'Content-Type'        => 'text/csv',
				'Content-Disposition' => 'attachment; filename="analytics.csv"',
			)
		);
	}
}
