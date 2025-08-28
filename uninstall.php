<?php
// Exit if accessed directly or if uninstall not requested by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Optional constant to prevent accidental data loss
if ( ! defined( 'ARTPULSE_DELETE_DATA' ) || ! ARTPULSE_DELETE_DATA ) {
	return;
}

global $wpdb;

try {
	// Remove plugin options
	delete_option( 'artpulse_settings' );
	delete_option( 'openai_api_key' );
	delete_option( 'ap_db_version' );

	// Remove transients beginning with artpulse_
	$transients = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_artpulse_' ) . '%'
		)
	);

	foreach ( $transients as $transient ) {
		$name = str_replace( '_transient_', '', $transient );
		delete_transient( $name );
	}

	// Remove user meta keys beginning with artpulse_
	$meta_keys = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s GROUP BY meta_key",
			$wpdb->esc_like( 'artpulse_' ) . '%'
		)
	);

	foreach ( $meta_keys as $meta_key ) {
		delete_metadata( 'user', 0, $meta_key, '', true );
	}

	// Drop custom plugin tables prefixed with ap_
	$tables = $wpdb->get_col(
		$wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$wpdb->esc_like( $wpdb->prefix . 'ap_' ) . '%'
		)
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
	}
} catch ( Exception $e ) {
	// Errors are suppressed during uninstall to avoid blocking deletion
}
