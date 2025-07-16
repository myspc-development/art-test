<?php
namespace ArtPulse\Core;

class OrgCrmManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_tables']);
    }

    public static function install_tables(): void
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $contacts = $wpdb->prefix . 'ap_crm_contacts';
        $donors   = $wpdb->prefix . 'ap_donations';
        $sql1 = "CREATE TABLE $contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            org_id INT,
            user_id BIGINT NULL,
            email VARCHAR(255),
            name VARCHAR(255),
            tags TEXT,
            first_seen DATETIME,
            last_active DATETIME,
            KEY org_id (org_id)
        ) $charset;";
        $sql2 = "CREATE TABLE $donors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            org_id INT,
            user_id BIGINT NULL,
            amount DECIMAL(6,2),
            method VARCHAR(20),
            donated_at DATETIME,
            KEY org_id (org_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);
        dbDelta($sql2);
    }

    public static function maybe_install_tables(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_crm_contacts';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_tables();
        }
    }
}
