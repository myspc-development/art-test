<?php
namespace ArtPulse\Core;

class DashboardAnalyticsLogger
{
    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'dashboard_events_log';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            event VARCHAR(50) NOT NULL,
            details VARCHAR(255) DEFAULT '',
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY event (event),
            KEY logged_at (logged_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'dashboard_events_log';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function log(int $user_id, string $event, string $details = ''): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'dashboard_events_log';
        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'event'     => $event,
            'details'   => $details,
            'logged_at' => current_time('mysql'),
        ]);
    }
}
