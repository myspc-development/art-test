<?php
namespace ArtPulse\Discovery;

class TrendingManager {
    public static function register(): void {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('init', [self::class, 'schedule_cron']);
        add_action('ap_trending_calculate', [self::class, 'calculate_scores']);
    }

    public static function schedule_cron(): void {
        if (!wp_next_scheduled('ap_trending_calculate')) {
            wp_schedule_event(time(), 'hourly', 'ap_trending_calculate');
        }
    }

    public static function install_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_trending_rankings';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            object_id BIGINT NOT NULL,
            object_type VARCHAR(20) NOT NULL,
            score FLOAT NOT NULL DEFAULT 0,
            calculated_at DATETIME NOT NULL,
            PRIMARY KEY (object_id, object_type)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_trending_rankings';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function calculate_scores(): void {
        // Basic calculation using post meta counts
        $posts = get_posts([
            'post_type'      => 'artpulse_artwork',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ]);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_trending_rankings';
        foreach ($posts as $id) {
            $views    = (int) get_post_meta($id, 'view_count', true);
            $wishlist = (int) get_post_meta($id, 'wishlist_count', true);
            $shares   = (int) get_post_meta($id, 'share_count', true);
            $comments = (int) get_comments_number($id);
            $score    = $views * 0.4 + $wishlist * 0.3 + $shares * 0.2 + $comments * 0.1;
            $wpdb->replace($table, [
                'object_id'     => $id,
                'object_type'   => 'artwork',
                'score'         => $score,
                'calculated_at' => current_time('mysql'),
            ]);
        }
    }

    public static function get_trending(int $limit = 20, string $type = 'artwork'): array {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_trending_rankings';
        $rows  = $wpdb->get_results($wpdb->prepare(
            "SELECT object_id, score FROM $table WHERE object_type = %s ORDER BY score DESC LIMIT %d",
            $type,
            $limit
        ));
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'    => (int) $row->object_id,
                'score' => (float) $row->score,
                'title' => get_the_title($row->object_id),
                'link'  => get_permalink($row->object_id),
            ];
        }
        return $items;
    }
}
