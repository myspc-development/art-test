<?php
namespace ArtPulse\Core;

class ActivityLogger
{
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_activity_logs';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            org_id BIGINT NULL,
            user_id BIGINT NULL,
            action_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL DEFAULT '',
            metadata LONGTEXT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY org_id (org_id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY logged_at (logged_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_activity_logs';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function log(?int $org_id, ?int $user_id, string $action_type, string $description, array $metadata = []): void
    {
        global $wpdb;
        if (!isset($wpdb)) {
            return;
        }
        $table = $wpdb->prefix . 'ap_activity_logs';
        $wpdb->insert($table, [
            'org_id'      => $org_id,
            'user_id'     => $user_id,
            'action_type' => $action_type,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
            'metadata'    => wp_json_encode($metadata),
            'logged_at'   => current_time('mysql'),
        ]);
    }
}
