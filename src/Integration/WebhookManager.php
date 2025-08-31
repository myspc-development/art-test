<?php
namespace ArtPulse\Integration;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Handles organization webhooks for external automations.
 */
class WebhookManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'init', array( self::class, 'maybe_install_tables' ) );
		add_action( 'artpulse_ticket_purchased', array( self::class, 'handle_ticket_purchased' ), 10, 4 );
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/webhooks/(?P<id>\d+)',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'callback'            => array( self::class, 'handle_webhooks' ),
				'permission_callback' => array( self::class, 'check_manage_org' ),
				'args'                => array(
					'id' => array( 'validate_callback' => 'absint' ),
				),
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/webhooks/(?P<id>\d+)/(?P<hid>\d+)',
			array(
				'methods'             => array( 'PUT', 'DELETE' ),
				'callback'            => array( self::class, 'handle_webhook_item' ),
				'permission_callback' => array( self::class, 'check_manage_org' ),
				'args'                => array(
					'id'  => array( 'validate_callback' => 'absint' ),
					'hid' => array( 'validate_callback' => 'absint' ),
				),
			)
		);
	}

	public static function check_manage_org(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function maybe_install_tables(): void {
		global $wpdb;

		$table  = $wpdb->prefix . 'ap_webhooks';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                org_id BIGINT NOT NULL,
                url VARCHAR(255) NOT NULL,
                events VARCHAR(255) NOT NULL,
                secret VARCHAR(64) NOT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                last_status VARCHAR(20) DEFAULT NULL,
                last_sent DATETIME DEFAULT NULL,
                PRIMARY KEY (id),
                KEY org_id (org_id)
            ) $charset;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		$log_table  = $wpdb->prefix . 'ap_webhook_logs';
		$exists_log = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $log_table ) );
		if ( $exists_log !== $log_table ) {
			artpulse_create_webhook_logs_table();
		}
	}

	public static function list_webhooks( WP_REST_Request $req ): WP_REST_Response {
		$org_id = absint( $req['id'] );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE org_id = %d", $org_id ), ARRAY_A );
		return \rest_ensure_response( $rows );
	}

	public static function handle_webhooks( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		return match ( $req->get_method() ) {
			'POST' => self::create_webhook( $req ),
			'GET'  => self::list_webhooks( $req ),
			default => new WP_Error( 'invalid_method', 'Method not allowed', array( 'status' => 405 ) ),
		};
	}

	public static function create_webhook( WP_REST_Request $req ) {
		$org_id = absint( $req['id'] );
		$url    = esc_url_raw( $req->get_param( 'url' ) );
		$events = (array) $req->get_param( 'events' );
		$active = intval( $req->get_param( 'active' ) ) ? 1 : 0;

		if ( ! $url || empty( $events ) ) {
			return new WP_Error( 'invalid', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		$secret = wp_generate_password( 32, false, false );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';

		$wpdb->insert(
			$table,
			array(
				'org_id' => $org_id,
				'url'    => $url,
				'events' => implode( ',', array_map( 'sanitize_text_field', $events ) ),
				'secret' => $secret,
				'active' => $active,
			)
		);

		return \rest_ensure_response(
			array(
				'id'     => $wpdb->insert_id,
				'secret' => $secret,
			)
		);
	}

	public static function update_webhook( WP_REST_Request $req ) {
		$org_id = absint( $req['id'] );
		$id     = absint( $req['hid'] );
		$data   = array();

		if ( $req->has_param( 'url' ) ) {
			$data['url'] = esc_url_raw( $req->get_param( 'url' ) );
		}

		if ( $req->has_param( 'events' ) ) {
			$data['events'] = implode( ',', array_map( 'sanitize_text_field', (array) $req->get_param( 'events' ) ) );
		}

		if ( $req->has_param( 'active' ) ) {
			$data['active'] = intval( $req->get_param( 'active' ) ) ? 1 : 0;
		}

		if ( ! $data ) {
			return new WP_Error( 'invalid', 'No updates provided.', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';
		$wpdb->update(
			$table,
			$data,
			array(
				'id'     => $id,
				'org_id' => $org_id,
			)
		);

		return \rest_ensure_response( array( 'updated' => true ) );
	}

	public static function delete_webhook( WP_REST_Request $req ) {
		$org_id = absint( $req['id'] );
		$id     = absint( $req['hid'] );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';
		$wpdb->delete(
			$table,
			array(
				'id'     => $id,
				'org_id' => $org_id,
			)
		);

		return \rest_ensure_response( array( 'deleted' => true ) );
	}

	public static function handle_webhook_item( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		return match ( $req->get_method() ) {
			'PUT'    => self::update_webhook( $req ),
			'DELETE' => self::delete_webhook( $req ),
			default  => new WP_Error( 'invalid_method', 'Method not allowed', array( 'status' => 405 ) ),
		};
	}

	public static function handle_ticket_purchased( int $user_id, int $event_id, int $ticket_id, int $qty ): void {
		$data   = array(
			'ticket_id' => $ticket_id,
			'event_id'  => $event_id,
			'buyer_id'  => $user_id,
			'quantity'  => $qty,
		);
		$org_id = 0;
		self::trigger_event( 'ticket_sold', $org_id, $data );
	}

	public static function trigger_event( string $event, int $org_id, array $data ): void {
		if ( function_exists( 'ap_trigger_webhooks' ) ) {
			ap_trigger_webhooks( $event, $org_id, $data );
		}
	}

	private static function send_webhook( object $webhook, string $event, array $data ): void {
		$payload = array(
			'event'     => $event,
			'timestamp' => current_time( 'mysql' ),
			'data'      => $data,
		);
		$json    = wp_json_encode( $payload );
		$sig     = hash_hmac( 'sha256', $json, $webhook->secret );

		$res = wp_remote_post(
			$webhook->url,
			array(
				'body'    => $json,
				'headers' => array(
					'Content-Type'         => 'application/json',
					'X-ArtPulse-Signature' => 'sha256=' . $sig,
				),
				'timeout' => 5,
			)
		);

		$status = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		$body   = is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_body( $res );

		self::insert_log( (int) $webhook->id, $status ? (string) $status : null, $body );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';
		$wpdb->update(
			$table,
			array(
				'last_status' => $status ? (string) $status : 'error',
				'last_sent'   => current_time( 'mysql' ),
			),
			array( 'id' => $webhook->id )
		);
	}

	/** @internal For tests */
	public static function insert_log_for_tests( int $subscription_id, ?string $status_code, ?string $response_body ): void {
		self::insert_log( $subscription_id, $status_code, $response_body );
	}

	private static function insert_log( int $subscription_id, ?string $status_code, ?string $response_body ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhook_logs';
		$wpdb->insert(
			$table,
			array(
				'subscription_id' => $subscription_id,
				'status_code'     => $status_code,
				'response_body'   => $response_body,
				'timestamp'       => current_time( 'mysql' ),
			)
		);
	}
}
