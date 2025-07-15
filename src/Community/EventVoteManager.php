<?php
namespace ArtPulse\Community;

class EventVoteManager {
    public static function register(): void {
        add_action('init', [self::class, 'maybe_install_table']);
    }

    public static function install_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            event_id BIGINT NOT NULL,
            vote_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_event (user_id,event_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function vote(int $event_id, int $user_id): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        $wpdb->replace($table, [
            'user_id'  => $user_id,
            'event_id' => $event_id,
            'vote_date'=> current_time('mysql'),
        ]);
        return self::get_votes($event_id);
    }

    public static function has_voted(int $event_id, int $user_id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id=%d AND event_id=%d", $user_id, $event_id));
    }

    public static function get_votes(int $event_id): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE event_id=%d", $event_id));
    }

    public static function get_top_voted(int $limit = 10): array {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_votes';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT event_id, COUNT(*) as cnt FROM $table WHERE vote_date > NOW() - INTERVAL 30 DAY GROUP BY event_id ORDER BY cnt DESC LIMIT %d",
            $limit
        ));
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'event_id' => (int)$r->event_id,
                'votes'    => (int)$r->cnt,
                'title'    => get_the_title($r->event_id),
                'link'     => get_permalink($r->event_id),
            ];
        }
        return $list;
    }
}
