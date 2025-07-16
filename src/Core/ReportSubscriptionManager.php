<?php
namespace ArtPulse\Core;

class ReportSubscriptionManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_org_report_subscriptions';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            org_id INT,
            email VARCHAR(255),
            frequency VARCHAR(10),
            format VARCHAR(10),
            report_type VARCHAR(20),
            last_sent DATETIME,
            KEY org_id (org_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_org_report_subscriptions';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }
}
