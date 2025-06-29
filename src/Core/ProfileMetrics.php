<?php
namespace ArtPulse\Core;

class ProfileMetrics
{
    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_profile_metrics';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            profile_id BIGINT NOT NULL,
            metric VARCHAR(20) NOT NULL,
            day DATE NOT NULL,
            count BIGINT NOT NULL DEFAULT 0,
            UNIQUE KEY profile_metric_day (profile_id, metric, day)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function register(): void
    {
        add_action('wp', [self::class, 'track_view']);
        add_action('ap_follow_added', [self::class, 'track_follow'], 10, 3);
    }

    public static function track_view(): void
    {
        if (is_singular('artpulse_artist') || is_singular('artpulse_org')) {
            global $post;
            if ($post) {
                self::log_metric($post->post_author, 'view');
            }
        }
    }

    public static function track_follow($user_id, $object_id, $object_type): void
    {
        if ($object_type === 'user') {
            self::log_metric($object_id, 'follow');
        }
    }

    public static function log_metric(int $profile_id, string $metric, int $amount = 1): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_profile_metrics';
        $day   = current_time('Y-m-d');
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET count = count + %d WHERE profile_id = %d AND metric = %s AND day = %s",
                $amount,
                $profile_id,
                $metric,
                $day
            )
        );
        if (!$updated) {
            $wpdb->insert(
                $table,
                [
                    'profile_id' => $profile_id,
                    'metric'     => $metric,
                    'day'        => $day,
                    'count'      => $amount,
                ],
                [ '%d', '%s', '%s', '%d' ]
            );
        }
        do_action('ap_profile_metric_logged', $profile_id, $metric, $amount);
    }

    public static function get_counts(int $profile_id, string $metric, int $days = 30): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_profile_metrics';
        $since = date('Y-m-d', strtotime('-' . $days . ' days'));
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT day, count FROM {$table} WHERE profile_id = %d AND metric = %s AND day >= %s ORDER BY day ASC",
                $profile_id,
                $metric,
                $since
            )
        );
        $output = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' days'));
            $output[$d] = 0;
        }
        foreach ($rows as $row) {
            $output[$row->day] = (int) $row->count;
        }
        return [ 'days' => array_keys($output), 'counts' => array_values($output) ];
    }
}
