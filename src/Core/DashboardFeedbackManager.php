<?php
namespace ArtPulse\Core;

use WP_Error;

class DashboardFeedbackManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'wp_ajax_ap_dashboard_feedback', array( self::class, 'handle' ) );
	}

	public static function install_table(): void {
		global $wpdb;
		$table   = $wpdb->prefix . 'dashboard_feedback';
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NULL,
            role VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY role (role)
        ) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'dashboard_feedback';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function handle(): void {
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_dashboard_feedback', 'nonce' );
		$message = sanitize_textarea_field( $_POST['message'] ?? '' );
		if ( $message === '' ) {
			wp_send_json_error( array( 'message' => __( 'Message required.', 'artpulse' ) ) );
		}
		$user_id = get_current_user_id();
		$role    = DashboardController::get_role( $user_id );
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'dashboard_feedback',
			array(
				'user_id'    => $user_id ?: null,
				'role'       => $role,
				'message'    => $message,
				'created_at' => current_time( 'mysql' ),
			)
		);
		$admin = get_option( 'admin_email' );
                if ( $admin && is_email( $admin ) ) {
                        $subject = sprintf( __( 'Dashboard feedback from %s', 'artpulse' ), $role );
                        $body    = sprintf( "%s\n\nUser ID: %d\nRole: %s\nTime: %s", $message, $user_id, $role, current_time( 'mysql' ) );
                        list( $admin, $subject, $body, $headers ) = apply_filters(
                                'wp_mail',
                                array( $admin, $subject, $body, array() )
                        );
                        wp_mail( $admin, $subject, $body, $headers );
                }
		wp_send_json_success( array( 'saved' => true ) );
	}
}
