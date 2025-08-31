<?php
namespace ArtPulse\Marketplace;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AuctionManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_tables' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function maybe_install_tables(): void {
		global $wpdb;
		$auctions = $wpdb->prefix . 'ap_auctions';
		$bids     = $wpdb->prefix . 'ap_bids';
		$exists   = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $auctions ) );
		if ( $exists !== $auctions ) {
			self::install_tables();
			return;
		}
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bids ) );
		if ( $exists !== $bids ) {
			self::install_tables();
		}
	}

	public static function install_tables(): void {
		\ArtPulse\DB\create_monetization_tables();
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/bids' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/bids',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'place_bid' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/bids/(?P<artwork_id>\\d+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/bids/(?P<artwork_id>\\d+)',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_bids' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'args'                => array(
						'artwork_id' => array( 'validate_callback' => 'absint' ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/auctions/live' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/auctions/live',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_live' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
				)
			);
		}
	}

	private static function get_tables(): array {
		global $wpdb;
		return array(
			$wpdb->prefix . 'ap_auctions',
			$wpdb->prefix . 'ap_bids',
		);
	}

	public static function place_bid( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		list($auction_table, $bid_table) = self::get_tables();
		global $wpdb;
		$artwork_id = absint( $req->get_param( 'artwork_id' ) );
		$amount     = floatval( $req->get_param( 'amount' ) );
		if ( ! $artwork_id || $amount <= 0 ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}
		$auction = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $auction_table WHERE artwork_id = %d AND is_active = 1", $artwork_id ), ARRAY_A );
		if ( ! $auction ) {
			return new WP_Error( 'no_auction', 'Auction not found', array( 'status' => 404 ) );
		}
		$now = current_time( 'timestamp' );
		if ( $now < strtotime( $auction['start_time'] ) || $now > strtotime( $auction['end_time'] ) ) {
			return new WP_Error( 'auction_closed', 'Auction closed', array( 'status' => 400 ) );
		}
		$highest = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(amount) FROM $bid_table WHERE artwork_id = %d", $artwork_id ) );
		$min     = $auction['starting_bid'];
		if ( $highest !== null ) {
			$min = max( $min, $highest + $auction['min_increment'] );
		}
		if ( $amount < $min ) {
			return new WP_Error( 'low_bid', 'Bid too low', array( 'status' => 400 ) );
		}
		$wpdb->insert(
			$bid_table,
			array(
				'user_id'    => get_current_user_id(),
				'artwork_id' => $artwork_id,
				'amount'     => $amount,
				'created_at' => current_time( 'mysql' ),
			)
		);
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function list_bids( WP_REST_Request $req ): WP_REST_Response {
		list($auction_table, $bid_table) = self::get_tables();
		global $wpdb;
		$artwork_id = absint( $req['artwork_id'] );
		$rows       = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $bid_table WHERE artwork_id = %d ORDER BY created_at DESC", $artwork_id ), ARRAY_A );
		return \rest_ensure_response( $rows );
	}

	public static function list_live(): WP_REST_Response {
		list($auction_table, ) = self::get_tables();
		global $wpdb;
		$now  = current_time( 'mysql' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $auction_table WHERE is_active = 1 AND start_time <= %s AND end_time >= %s", $now, $now ), ARRAY_A );
		return \rest_ensure_response( $rows );
	}
}
