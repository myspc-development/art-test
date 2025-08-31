<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;
use WP_Error;

/**
 * Manages artist payouts and settings.
 */
class PayoutManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/payouts' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/payouts',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_payouts' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/payouts/settings' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/payouts/settings',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'update_settings' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
				)
			);
		}
	}

	public static function check_logged_in() {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_payouts';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
                artist_id BIGINT NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                method VARCHAR(50) NOT NULL DEFAULT '',
                payout_date DATETIME NOT NULL,
                KEY artist_id (artist_id)
            ) $charset;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			file_put_contents(
				plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'install.log',
				'[' . current_time( 'mysql' ) . "] Created table $table\n",
				FILE_APPEND
			);
		}
	}

	public static function list_payouts(): \WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		global $wpdb;
		$table = $wpdb->prefix . 'ap_payouts';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return \rest_ensure_response(
				array(
					'payouts' => array(),
					'balance' => 0,
				)
			);
		}
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE artist_id = %d ORDER BY payout_date DESC", $user_id ), ARRAY_A );

		$balance = self::get_balance( $user_id );

		return \rest_ensure_response(
			array(
				'payouts' => $rows,
				'balance' => round( $balance, 2 ),
			)
		);
	}

	public static function get_balance( int $artist_id ): float {
		global $wpdb;
		$tickets = $wpdb->prefix . 'ap_tickets';
		$tiers   = $wpdb->prefix . 'ap_event_tickets';
		$posts   = $wpdb->posts;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tickets ) ) !== $tickets ||
			$wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tiers ) ) !== $tiers ) {
			return 0.0;
		}
		$sql         = "SELECT SUM(et.price) FROM $tickets t JOIN $tiers et ON t.ticket_tier_id = et.id JOIN $posts p ON t.event_id = p.ID WHERE p.post_author = %d AND t.status = 'active'";
		$sales_total = floatval( $wpdb->get_var( $wpdb->prepare( $sql, $artist_id ) ) );

		$payouts = $wpdb->prefix . 'ap_payouts';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $payouts ) ) !== $payouts ) {
			return 0.0;
		}

		$payout_total = floatval( $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM $payouts WHERE artist_id = %d AND status = 'paid'", $artist_id ) ) );

		return (float) ( $sales_total - $payout_total );
	}

	public static function update_settings( WP_REST_Request $req ) {
		$method = sanitize_text_field( $req->get_param( 'method' ) );
		if ( ! $method ) {
			return new WP_Error( 'invalid_method', 'Invalid payout method.', array( 'status' => 400 ) );
		}
		update_user_meta( get_current_user_id(), 'ap_payout_method', $method );
		return \rest_ensure_response( array( 'method' => $method ) );
	}
}
