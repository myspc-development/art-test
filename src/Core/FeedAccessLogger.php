<?php
namespace ArtPulse\Core;

class FeedAccessLogger
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feed_logs';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NULL,
            feed_type VARCHAR(10) NOT NULL,
            query_hash VARCHAR(32) NOT NULL,
            requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY feed_type (feed_type),
            KEY requested_at (requested_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feed_logs';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function log(string $type, string $hash, int $user_id = 0): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feed_logs';
        $wpdb->insert($table, [
            'user_id'      => $user_id,
            'feed_type'    => $type,
            'query_hash'   => $hash,
            'requested_at' => current_time('mysql'),
        ]);
    }
}
