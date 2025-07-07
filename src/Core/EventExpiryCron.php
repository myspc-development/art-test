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
        $expired = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => 'event_end_date',
                    'value'   => $today,
                    'compare' => '<',
                    'type'    => 'DATE',
                ],
            ],
        ]);

        foreach ($expired as $post_id) {
            wp_update_post([
                'ID'          => $post_id,
                'post_status' => 'draft',
            ]);
        }
    }
}
