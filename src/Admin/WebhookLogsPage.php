<?php
namespace ArtPulse\Admin;

/**
 * Display webhook delivery logs in wp-admin.
 */
class WebhookLogsPage {

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
	}

	public static function addMenu(): void {
		add_submenu_page(
			'artpulse-settings',
			__( 'Webhook Logs', 'artpulse' ),
			__( 'Webhook Logs', 'artpulse' ),
			'manage_options',
			'ap-webhook-logs',
			array( self::class, 'render' )
		);
	}

	public static function render(): void {
               global $wpdb;
               $table   = $wpdb->prefix . 'ap_webhook_logs';
               $columns = 'id, timestamp, subscription_id, status_code';
               $rows    = $wpdb->get_results( "SELECT $columns FROM $table ORDER BY id DESC LIMIT 200" );
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Webhook Delivery Logs', 'artpulse' ) . '</h1>';
		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Time', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Subscription', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'artpulse' ) . '</th>';
		echo '</tr></thead><tbody>';
		if ( $rows ) {
			foreach ( $rows as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->timestamp ) ) ) . '</td>';
				echo '<td>' . esc_html( $row->subscription_id ) . '</td>';
				echo '<td>' . esc_html( $row->status_code ) . '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="3">' . esc_html__( 'No logs found.', 'artpulse' ) . '</td></tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
	}
}
