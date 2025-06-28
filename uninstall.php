<?php
// In uninstall.php at plugin root:
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options.
delete_option( 'artpulse_settings' );

// Drop custom DB tables.
global $wpdb;
$table_name = $wpdb->prefix . 'artpulse_data';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
