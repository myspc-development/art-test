<?php
namespace ArtPulse\Util;

use WP_Query;

/**
 * Fetch events for calendar feeds with optional location filtering.
 *
 * @param float|null $lat Latitude to filter by.
 * @param float|null $lng Longitude to filter by.
 * @return array[] List of event data.
 */
function ap_fetch_calendar_events($lat = null, $lng = null): array
{
    $meta_query = [
        ['key' => 'event_start_date', 'compare' => 'EXISTS'],
    ];

    if ($lat !== null && $lng !== null) {
        $lat = (float) $lat;
        $lng = (float) $lng;
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

    $query = new WP_Query([
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
    return $events;
}
