<?php
namespace ArtPulse\Core;

class UserEngagementLogger
{
    public static function install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_engagement_log';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NOT NULL,
            type VARCHAR(20) NOT NULL,
            event_id BIGINT NOT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY event_id (event_id),
            KEY type (type),
            KEY logged_at (logged_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Ensure the engagement log table exists.
     */
    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_user_engagement_log';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function log(int $user_id, string $type, int $event_id): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_engagement_log';
        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'type'      => $type,
            'event_id'  => $event_id,
            'logged_at' => current_time('mysql'),
        ]);
    }

    public static function get_stats(int $user_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_user_engagement_log';
        $since = date('Y-m-d H:i:s', strtotime('-30 days'));
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(logged_at) as day, type, COUNT(*) as cnt FROM $table WHERE user_id = %d AND logged_at >= %s GROUP BY DATE(logged_at), type",
                $user_id,
                $since
            )
        );

        $days = [];
        $rsvp_daily = [];
        $fav_daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $days[] = $d;
            $rsvp_daily[$d] = 0;
            $fav_daily[$d] = 0;
        }

        foreach ($rows as $row) {
            if ($row->type === 'rsvp' && isset($rsvp_daily[$row->day])) {
                $rsvp_daily[$row->day] = (int) $row->cnt;
            }
            if ($row->type === 'favorite' && isset($fav_daily[$row->day])) {
                $fav_daily[$row->day] = (int) $row->cnt;
            }
        }

        $weeks = [];
        $rsvp_weekly = array_fill(0, 5, 0);
        $fav_weekly  = array_fill(0, 5, 0);
        foreach (array_values($days) as $index => $day) {
            $week = intdiv($index, 7);
            $weeks[$week] = $weeks[$week] ?? $day;
            $rsvp_weekly[$week] += $rsvp_daily[$day];
            $fav_weekly[$week]  += $fav_daily[$day];
        }
        $week_labels = array_values($weeks);

        return [
            'days'          => $days,
            'rsvp_daily'    => array_values($rsvp_daily),
            'favorite_daily'=> array_values($fav_daily),
            'weeks'         => $week_labels,
            'rsvp_weekly'   => $rsvp_weekly,
            'favorite_weekly' => $fav_weekly,
        ];
    }
}
