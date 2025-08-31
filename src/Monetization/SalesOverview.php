<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;
use WP_Error;

/**
 * Provides sales stats for artists.
 */
class SalesOverview {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/sales' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/sales',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_sales' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
				)
			);
		}
	}

	public static function check_logged_in() {
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function get_sales( WP_REST_Request $req ) {
		$user_id  = get_current_user_id();
		$event_id = absint( $req->get_param( 'event_id' ) );
		$from     = sanitize_text_field( $req->get_param( 'from' ) );
		$to       = sanitize_text_field( $req->get_param( 'to' ) );

		global $wpdb;
		$tickets = $wpdb->prefix . 'ap_tickets';
		$tiers   = $wpdb->prefix . 'ap_event_tickets';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tickets ) ) !== $tickets ||
			$wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tiers ) ) !== $tiers ) {
			return \rest_ensure_response(
				array(
					'tickets_sold'  => 0,
					'total_revenue' => 0,
					'trend'         => array(),
				)
			);
		}
		$posts = $wpdb->posts;

		$where  = $wpdb->prepare( 'p.post_author = %d', $user_id );
		$params = array();
		if ( $event_id ) {
			$where .= $wpdb->prepare( ' AND t.event_id = %d', $event_id );
		}
		if ( $from ) {
			$where .= $wpdb->prepare( ' AND t.purchase_date >= %s', $from );
		}
		if ( $to ) {
			$where .= $wpdb->prepare( ' AND t.purchase_date <= %s', $to );
		}

		$query = "SELECT COUNT(t.id) AS tickets, SUM(et.price) AS revenue, DATE(t.purchase_date) AS day
                  FROM $tickets t
                  JOIN $tiers et ON t.ticket_tier_id = et.id
                  JOIN $posts p ON t.event_id = p.ID
                  WHERE $where AND t.status = 'active'
                  GROUP BY day";

		$rows          = $wpdb->get_results( $query, ARRAY_A );
		$total_tickets = 0;
		$total_revenue = 0.0;
		$trend         = array();
		foreach ( $rows as $row ) {
			$tickets_sold   = intval( $row['tickets'] );
			$revenue        = floatval( $row['revenue'] );
			$trend[]        = array(
				'date'    => $row['day'],
				'tickets' => $tickets_sold,
				'revenue' => $revenue,
			);
			$total_tickets += $tickets_sold;
			$total_revenue += $revenue;
		}

		return \rest_ensure_response(
			array(
				'tickets_sold'  => $total_tickets,
				'total_revenue' => round( $total_revenue, 2 ),
				'trend'         => $trend,
			)
		);
	}
}
