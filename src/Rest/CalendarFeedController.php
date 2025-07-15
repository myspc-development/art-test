<?php
namespace ArtPulse\Rest;

use WP_REST_Request;

class CalendarFeedController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/calendar', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_feed'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function get_feed(WP_REST_Request $req)
    {
        $lat = $req->get_param('lat');
        $lng = $req->get_param('lng');

        $meta_query = [
            ['key' => 'event_start_date', 'compare' => 'EXISTS'],
        ];

        if ($lat && $lng) {
            $lat = floatval($lat);
            $lng = floatval($lng);
            $meta_query[] = [
                'key'     => 'event_lat',
                'value'   => [ $lat - 0.5, $lat + 0.5 ],
                'compare' => 'BETWEEN',
                'type'    => 'DECIMAL(10,6)',
            ];
            $meta_query[] = [
                'key'     => 'event_lng',
                'value'   => [ $lng - 0.5, $lng + 0.5 ],
                'compare' => 'BETWEEN',
                'type'    => 'DECIMAL(10,6)',
            ];
        }

        $query = new \WP_Query([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'meta_query'     => $meta_query,
        ]);
        $user_id   = get_current_user_id();
        $favorited = $user_id ? (array) get_user_meta($user_id, 'ap_favorite_events', true) : [];
        $rsvpd     = $user_id ? (array) get_user_meta($user_id, 'ap_rsvp_events', true) : [];

        $events = [];
        while ($query->have_posts()) {
            $query->the_post();
            $event_id = get_the_ID();
            $start    = get_post_meta($event_id, 'event_start_date', true);
            $end      = get_post_meta($event_id, 'event_end_date', true);
            $venue    = get_post_meta($event_id, 'venue_name', true);
            $address  = get_post_meta($event_id, 'event_street_address', true);

            $is_fav  = in_array($event_id, $favorited, true);
            $is_rsvp = in_array($event_id, $rsvpd, true);

            $class = [];
            if ($is_fav) {
                $class[] = 'event-favorited';
            }
            if ($is_rsvp) {
                $class[] = 'event-rsvpd';
            }

            $events[] = [
                'id'    => $event_id,
                'title' => get_the_title(),
                'start' => $start,
                'end'   => $end,
                'url'   => get_permalink(),
                'classNames' => $class,
                'extendedProps' => [
                    'venue'     => $venue,
                    'address'   => $address,
                    'favorited' => $is_fav,
                    'rsvpd'     => $is_rsvp,
                ],
            ];
        }
        wp_reset_postdata();
        return rest_ensure_response($events);
    }
}
