<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$settings = get_option('artpulse_settings', []);
if (!empty($settings['keep_data_on_uninstall'])) {
    return;
}

$tables = [
    'ap_roles',
    'ap_feedback',
    'ap_feedback_comments',
    'ap_org_messages',
    'ap_scheduled_messages',
    'ap_payouts',
    'ap_org_roles',
    'ap_donations',
    'ap_tickets',
    'ap_event_tickets',
    'ap_auctions',
    'ap_bids',
    'ap_promotions',
    'ap_messages',
    'ap_org_user_roles',
];

foreach ($tables as $t) {
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$t}");
}

delete_option('artpulse_settings');
delete_option('artpulse_version');
delete_option('ap_db_version');
delete_option('ap_latest_release_info');
delete_option('ap_update_available');

