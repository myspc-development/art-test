<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;
use WP_Error;

class DonationManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/donations' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/donations',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'create_donation' ),
					'permission_callback' => array( self::class, 'check_logged_in' ),
					'args'                => array(
						'artist_id' => array(
							'validate_callback' => 'absint',
							'required'          => true,
						),
						'amount'    => array(
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
							'required'          => true,
						),
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
		$table  = $wpdb->prefix . 'ap_donations';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                artist_id BIGINT NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                note TEXT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY artist_id (artist_id),
                KEY user_id (user_id)
            ) $charset;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	public static function create_donation( WP_REST_Request $req ) {
		$artist_id = absint( $req->get_param( 'artist_id' ) );
		$amount    = floatval( $req->get_param( 'amount' ) );
		$note      = sanitize_text_field( $req->get_param( 'note' ) );
		$user_id   = get_current_user_id();

		if ( ! $artist_id || $amount <= 0 ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_donations';
		$wpdb->insert(
			$table,
			array(
				'user_id'    => $user_id,
				'artist_id'  => $artist_id,
				'amount'     => $amount,
				'note'       => $note,
				'created_at' => current_time( 'mysql' ),
			)
		);

		return \rest_ensure_response( array( 'success' => true ) );
	}
}
