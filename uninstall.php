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
delete_option('artpulse_default_layouts');
delete_option('artpulse_widget_roles');
delete_option('artpulse_locked_widgets');

$dash_roles = ['artist', 'member', 'organization'];
$users = get_users(['role__in' => $dash_roles]);
foreach ($users as $user) {
    delete_user_meta($user->ID, 'ap_dashboard_layout');
    $user->set_role('subscriber');
}
foreach ($dash_roles as $r) {
    remove_role($r);
}

