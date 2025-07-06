<?php
namespace ArtPulse\Personalization;

use ArtPulse\Community\FavoritesManager;

class RecommendationEngine
{
    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_user_activity';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_activity';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            object_type VARCHAR(20) NOT NULL,
            object_id BIGINT NOT NULL,
            action VARCHAR(20) NOT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY object_id (object_id),
            KEY object_type (object_type),
            KEY action (action),
            KEY logged_at (logged_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function log(int $user_id, string $object_type, int $object_id, string $action): void
    {
        if (!$user_id) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_activity';
        $wpdb->insert($table, [
            'user_id'     => $user_id,
            'object_type' => $object_type,
            'object_id'   => $object_id,
            'action'      => $action,
            'logged_at'   => current_time('mysql'),
        ]);
    }

    public static function get_viewed_objects(int $user_id, string $object_type): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_activity';
        $since = date('Y-m-d H:i:s', strtotime('-30 days'));
        $sql   = "SELECT object_id FROM $table WHERE user_id = %d AND object_type = %s AND action = 'view' AND logged_at >= %s";
        return array_map('intval', $wpdb->get_col($wpdb->prepare($sql, $user_id, $object_type, $since)));
    }

    public static function get_recommendations(int $user_id, string $type = 'event', int $limit = 5): array
    {
        $cache_key = "ap_rec_{$type}_{$user_id}";
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $recs = [];
        if ($type === 'event') {
            $fav = FavoritesManager::get_user_favorites($user_id, 'artpulse_event');
            $fav_ids = array_map(fn($f) => (int) $f->object_id, $fav);
            $rsvp_ids = get_user_meta($user_id, 'ap_rsvp_events', true) ?: [];
            $view_ids = self::get_viewed_objects($user_id, 'event');
            $seed_ids = array_unique(array_merge($fav_ids, $rsvp_ids, $view_ids));

            $terms = [];
            foreach ($seed_ids as $eid) {
                $terms = array_merge($terms, wp_get_object_terms($eid, ['artpulse_category', 'post_tag'], ['fields' => 'ids']));
            }
            $terms = array_unique($terms);

            if ($terms) {
                $query = new \WP_Query([
                    'post_type'      => 'artpulse_event',
                    'post__not_in'   => $seed_ids,
                    'tax_query'      => [
                        [
                            'taxonomy' => 'artpulse_category',
                            'field'    => 'term_id',
                            'terms'    => $terms,
                            'operator' => 'IN',
                        ],
                    ],
                    'posts_per_page' => $limit,
                    'no_found_rows'  => true,
                ]);
                foreach ($query->posts as $post) {
                    $recs[] = [
                        'id'     => $post->ID,
                        'title'  => $post->post_title,
                        'score'  => 1,
                        'reason' => __('Based on your favorites', 'artpulse'),
                    ];
                }
            }

            if (!$recs) {
                $query = new \WP_Query([
                    'post_type'      => 'artpulse_event',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'no_found_rows'  => true,
                ]);
                $events = $query->posts;
                usort($events, function($a, $b) {
                    $fav_a = (int) get_post_meta($a->ID, 'ap_favorite_count', true);
                    $fav_b = (int) get_post_meta($b->ID, 'ap_favorite_count', true);
                    if ($fav_a === $fav_b) {
                        $rsvp_a = count((array) get_post_meta($a->ID, 'event_rsvp_list', true));
                        $rsvp_b = count((array) get_post_meta($b->ID, 'event_rsvp_list', true));
                        if ($rsvp_a === $rsvp_b) {
                            $view_a = (int) get_post_meta($a->ID, 'view_count', true);
                            $view_b = (int) get_post_meta($b->ID, 'view_count', true);
                            return $view_b <=> $view_a;
                        }
                        return $rsvp_b <=> $rsvp_a;
                    }
                    return $fav_b <=> $fav_a;
                });
                $events = array_slice($events, 0, $limit);
                foreach ($events as $post) {
                    $recs[] = [
                        'id'     => $post->ID,
                        'title'  => $post->post_title,
                        'score'  => 0.5,
                        'reason' => __('Trending', 'artpulse'),
                    ];
                }
            }
        }

        set_transient($cache_key, $recs, 15 * MINUTE_IN_SECONDS);
        return $recs;
    }
}
