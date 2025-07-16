<?php
namespace ArtPulse\Core;

class EventExpiryCron
{
    public static function register()
    {
        add_action('ap_daily_expiry_check', [self::class, 'checkExpiredEvents']);
    }

    public static function checkExpiredEvents()
    {
        $opts = get_option('artpulse_settings', []);
        if (empty($opts['auto_expire_events'])) {
            return;
        }

        $today = date('Y-m-d');
        $page = 1;
        do {
            $query = new \WP_Query([
                'post_type'      => 'artpulse_event',
                'post_status'    => 'publish',
                'posts_per_page' => 100,
                'fields'         => 'ids',
                'paged'          => $page,
                'meta_query'     => [
                    [
                        'key'     => 'event_end_date',
                        'value'   => $today,
                        'compare' => '<',
                        'type'    => 'DATE',
                    ],
                ],
                'no_found_rows'  => true,
            ]);

            foreach ($query->posts as $post_id) {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                ]);
            }
            $page++;
        } while ($query->max_num_pages >= $page);
    }
}
