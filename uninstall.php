<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$tables = [
    'ap_roles',
    'ap_feedback',
    'ap_feedback_comments',
    'ap_org_messages',
    'ap_scheduled_messages',
    'ap_payouts',
];

foreach ($tables as $t) {
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$t}");
}

delete_option('artpulse_settings');
delete_option('artpulse_version');

