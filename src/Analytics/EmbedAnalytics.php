<?php
namespace ArtPulse\Analytics;

use WP_REST_Request;
use WP_REST_Response;

class EmbedAnalytics {
	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_embed_logs';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'ap_embed_logs';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            widget_id VARCHAR(40) NOT NULL,
            event_id BIGINT NOT NULL DEFAULT 0,
            timestamp DATETIME NOT NULL,
            referrer TEXT NULL,
            action VARCHAR(10) NOT NULL DEFAULT 'view',
            PRIMARY KEY (id),
            KEY widget_id (widget_id),
            KEY event_id (event_id),
            KEY action (action)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/widgets/log',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'log_endpoint' ),
				'permission_callback' => function ( WP_REST_Request $req ): bool {
					$nonce = $req->get_header( 'X-WP-Nonce' );
					return (bool) ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) && current_user_can( 'read' ) );
				},
			)
		);
	}

	public static function log_endpoint( WP_REST_Request $req ): WP_REST_Response {
		$widget = sanitize_text_field( $req->get_param( 'widget_id' ) );
		$event  = absint( $req->get_param( 'event_id' ) );
		$action = sanitize_key( $req->get_param( 'action' ) ?: 'view' );
		self::log( $widget, $event, $action );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function log( string $widget_id, int $event_id = 0, string $action = 'view' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_embed_logs';
		$wpdb->insert(
			$table,
			array(
				'widget_id' => substr( $widget_id, 0, 40 ),
				'event_id'  => $event_id,
				'action'    => $action,
				'referrer'  => $_SERVER['HTTP_REFERER'] ?? '',
				'timestamp' => current_time( 'mysql' ),
			)
		);
	}
}
