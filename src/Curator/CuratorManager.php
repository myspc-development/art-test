<?php
namespace ArtPulse\Curator;

class CuratorManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_curators';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            bio TEXT NULL,
            website VARCHAR(255) NULL,
            social_links TEXT NULL,
            UNIQUE KEY slug (slug),
            KEY user_id (user_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_curators';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function get_by_slug(string $slug): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_curators';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE slug = %s", $slug), ARRAY_A);
        return $row ?: null;
    }

    public static function get_all(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_curators';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC", ARRAY_A);
    }
}
