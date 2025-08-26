<?php
namespace ArtPulse\Personalization;

class RecommendationPreferenceManager
{
    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_preferences';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            user_id BIGINT(20) NOT NULL,
            preferred_tags LONGTEXT NULL,
            ignored_tags LONGTEXT NULL,
            blacklist_ids LONGTEXT NULL,
            PRIMARY KEY  (user_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_user_preferences';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function get(int $user_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_preferences';
        $row   = $wpdb->get_row($wpdb->prepare("SELECT preferred_tags, ignored_tags, blacklist_ids FROM $table WHERE user_id = %d", $user_id), ARRAY_A);
        return [
            'preferred_tags' => $row ? json_decode($row['preferred_tags'], true) ?: [] : [],
            'ignored_tags'   => $row ? json_decode($row['ignored_tags'], true) ?: [] : [],
            'blacklist_ids'  => $row ? json_decode($row['blacklist_ids'], true) ?: [] : [],
        ];
    }

    public static function update(int $user_id, array $prefs): void
    {
        $current = self::get($user_id);
        $prefs = array_merge($current, $prefs);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_preferences';
        $wpdb->replace($table, [
            'user_id'       => $user_id,
            'preferred_tags'=> wp_json_encode(array_values(array_unique((array) $prefs['preferred_tags']))),
            'ignored_tags'  => wp_json_encode(array_values(array_unique((array) $prefs['ignored_tags']))),
            'blacklist_ids' => wp_json_encode(array_map('intval', (array) $prefs['blacklist_ids'])),
        ]);
    }
}
