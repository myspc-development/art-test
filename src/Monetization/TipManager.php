<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;
use WP_Error;

/**
 * Records artist tip transactions.
 */
class TipManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artist/(?P<id>\\d+)/tip' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/artist/(?P<id>\\d+)/tip',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'record_tip' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
					'args'                => array(
						'id'     => array( 'validate_callback' => 'absint' ),
						'amount' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
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
		$table  = $wpdb->prefix . 'ap_tips';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id),
                artist_id BIGINT NOT NULL,
                user_id BIGINT NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                tip_date DATETIME NOT NULL,
                KEY artist_id (artist_id),
                KEY user_id (user_id)
            ) $charset;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	public static function record_tip( WP_REST_Request $req ) {
		$artist_id = absint( $req->get_param( 'id' ) );
		$amount    = floatval( $req->get_param( 'amount' ) );
		$user_id   = get_current_user_id();

		if ( ! $artist_id || $amount <= 0 ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_tips';
		$wpdb->insert(
			$table,
			array(
				'artist_id' => $artist_id,
				'user_id'   => $user_id,
				'amount'    => $amount,
				'tip_date'  => current_time( 'mysql' ),
			)
		);

		return \rest_ensure_response( array( 'success' => true ) );
	}
}
