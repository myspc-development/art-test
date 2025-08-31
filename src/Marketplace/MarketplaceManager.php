<?php
namespace ArtPulse\Marketplace;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class MarketplaceManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_tables' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function maybe_install_tables(): void {
		global $wpdb;
		$artworks = $wpdb->prefix . 'ap_market_artworks';
		$orders   = $wpdb->prefix . 'ap_market_orders';
		$exists   = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $artworks ) );
		if ( $exists !== $artworks ) {
			self::install_artworks_table();
		}
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $orders ) );
		if ( $exists !== $orders ) {
			self::install_orders_table();
		}
	}

	public static function install_artworks_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_market_artworks';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            artist_id BIGINT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            tags TEXT NULL,
            medium VARCHAR(100) NULL,
            dimensions VARCHAR(100) NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            edition_type VARCHAR(20) NULL,
            stock INT NOT NULL DEFAULT 0,
            shipping TINYINT(1) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY artist_id (artist_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function install_orders_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_market_orders';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            buyer_id BIGINT NOT NULL,
            artwork_id BIGINT NOT NULL,
            artist_id BIGINT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            payment_method VARCHAR(20) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY buyer_id (buyer_id),
            KEY artwork_id (artwork_id)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $sql ); }
		dbDelta( $sql );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artworks' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/artworks',
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'create_artwork' ),
						'permission_callback' => array( self::class, 'check_artist' ),
					),
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'list_artworks' ),
						'permission_callback' => function () {
							return current_user_can( 'read' );
						},
					),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artworks/(?P<id>\\d+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/artworks/(?P<id>\\d+)',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_artwork' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orders' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orders',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'submit_order' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orders/mine' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orders/mine',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_orders' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}
	}

	public static function check_artist(): bool {
		return current_user_can( 'artist' );
	}

	public static function create_artwork( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$data = array(
			'artist_id'    => get_current_user_id(),
			'title'        => sanitize_text_field( $req->get_param( 'title' ) ),
			'description'  => sanitize_textarea_field( $req->get_param( 'description' ) ),
			'tags'         => sanitize_text_field( $req->get_param( 'tags' ) ),
			'medium'       => sanitize_text_field( $req->get_param( 'medium' ) ),
			'dimensions'   => sanitize_text_field( $req->get_param( 'dimensions' ) ),
			'price'        => floatval( $req->get_param( 'price' ) ),
			'edition_type' => sanitize_text_field( $req->get_param( 'edition_type' ) ),
			'stock'        => intval( $req->get_param( 'stock' ) ),
			'shipping'     => $req->get_param( 'shipping' ) ? 1 : 0,
			'status'       => 'active',
		);
		if ( $data['title'] === '' ) {
			return new WP_Error( 'invalid_title', 'Title required.', array( 'status' => 400 ) );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ap_market_artworks';
		$wpdb->insert( $table, $data );
		$id = (int) $wpdb->insert_id;
		return \rest_ensure_response( array( 'id' => $id ) );
	}

	public static function list_artworks(): WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_market_artworks';
		$rows  = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active' ORDER BY created_at DESC", ARRAY_A );
		return \rest_ensure_response( $rows );
	}

	public static function get_artwork( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$id = absint( $req['id'] );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_market_artworks';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'Artwork not found', array( 'status' => 404 ) );
		}
		return \rest_ensure_response( $row );
	}

	public static function submit_order( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$artwork_id = absint( $req->get_param( 'artwork_id' ) );
		$qty        = max( 1, intval( $req->get_param( 'quantity' ) ) );
		$method     = sanitize_text_field( $req->get_param( 'payment_method' ) );
		global $wpdb;
		$artwork_table = $wpdb->prefix . 'ap_market_artworks';
		$artwork       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $artwork_table WHERE id = %d", $artwork_id ), ARRAY_A );
		if ( ! $artwork ) {
			return new WP_Error( 'invalid_artwork', 'Invalid artwork.', array( 'status' => 404 ) );
		}
		if ( $artwork['stock'] > 0 && $qty > $artwork['stock'] ) {
			return new WP_Error( 'insufficient_stock', 'Not enough stock.', array( 'status' => 409 ) );
		}
		$total       = $qty * floatval( $artwork['price'] );
		$order_table = $wpdb->prefix . 'ap_market_orders';
		$wpdb->insert(
			$order_table,
			array(
				'buyer_id'       => get_current_user_id(),
				'artwork_id'     => $artwork_id,
				'artist_id'      => intval( $artwork['artist_id'] ),
				'quantity'       => $qty,
				'total_price'    => $total,
				'payment_method' => $method,
				'status'         => 'paid',
				'created_at'     => current_time( 'mysql' ),
			)
		);
		$order_id = (int) $wpdb->insert_id;
		if ( $artwork['stock'] > 0 ) {
			$wpdb->update( $artwork_table, array( 'stock' => $artwork['stock'] - $qty ), array( 'id' => $artwork_id ) );
		}
		return \rest_ensure_response( array( 'order_id' => $order_id ) );
	}

	public static function list_orders(): WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_market_orders';
		$user  = get_current_user_id();
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE buyer_id = %d ORDER BY created_at DESC", $user ), ARRAY_A );
		return \rest_ensure_response( $rows );
	}
}
