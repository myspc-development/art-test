<?php
// In uninstall.php at plugin root:
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$opts   = get_option( 'artpulse_settings', [] );
$remove = empty( $opts['keep_data_on_uninstall'] );

if ( defined( 'ARTPULSE_REMOVE_DATA_ON_UNINSTALL' ) ) {
    $remove = (bool) ARTPULSE_REMOVE_DATA_ON_UNINSTALL;
}

if ( ! $remove ) {
    return;
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
    'ap_profile_metrics',
    'ap_link_requests_meta',
    'ap_roles',
    'ap_login_events',
    'ap_messages',
    'ap_artwork_event_links',
    'ap_feedback',
    'ap_feedback_comments',
    'ap_event_tickets',
    'ap_tickets',
    'ap_payouts',
    'ap_user_activity',
    'ap_event_chat',
    'ap_role_audit',
    'ap_user_engagement_log',
    'ap_activity_logs',
    'ap_event_metrics',
    'ap_delegated_access',
    'ap_competition_entries',
    'ap_scheduled_messages',
    'ap_org_messages',
    'ap_event_notes',
    'ap_event_tasks',
    'ap_webhooks',
];

foreach ( $tables as $table ) {
    $wpdb->query( sprintf( 'DROP TABLE IF EXISTS %s', $wpdb->prefix . $table ) );
}
