<?php
namespace ArtPulse\Core;

class PortfolioSyncLogger
{
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_portfolio_sync_logs';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NULL,
            action VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            metadata LONGTEXT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY action (action),
            KEY logged_at (logged_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_portfolio_sync_logs';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function log(string $action, string $message, array $metadata = [], ?int $user_id = null): void
    {
        global $wpdb;
        if (!isset($wpdb)) {
            return;
        }

        $table  = $wpdb->prefix . 'ap_portfolio_sync_logs';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            return; // Table hasn't been installed yet (e.g. during tests)
        }

        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'action'    => $action,
            'message'   => $message,
            'metadata'  => wp_json_encode($metadata),
            'logged_at' => current_time('mysql'),
        ]);
    }
}
