<?php
// In uninstall.php at plugin root:
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options.
delete_option( 'artpulse_settings' );

// Drop custom DB tables.
global $wpdb;

$tables = [
    'artpulse_data',       // legacy table
    'ap_favorites',
    'ap_follows',
    'ap_notifications',
    'ap_link_requests_meta',
    'ap_roles',
    'ap_login_events',
];

foreach ( $tables as $table ) {
    $wpdb->query( sprintf( 'DROP TABLE IF EXISTS %s', $wpdb->prefix . $table ) );
}
