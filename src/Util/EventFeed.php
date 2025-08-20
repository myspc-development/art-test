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
function ap_fetch_calendar_events($lat = null, $lng = null, $radius_km = null, $start = null, $end = null): array
{
    $meta_query = [
        ['key' => 'event_start_date', 'compare' => 'EXISTS'],
    ];

    if ($start) {
        $meta_query[] = [
            'key'     => 'event_start_date',
            'value'   => $start,
            'compare' => '>=',
            'type'    => 'DATE',
        ];
    }
    if ($end) {
        $meta_query[] = [
            'key'     => 'event_start_date',
            'value'   => $end,
            'compare' => '<=',
            'type'    => 'DATE',
        ];
    }

    if ($lat !== null && $lng !== null) {
        $lat = (float) $lat;
        $lng = (float) $lng;
        $radius_km = $radius_km !== null ? (float) $radius_km : 50.0;
        $lat_delta = $radius_km / 111.0;
        $lng_delta = $radius_km / (111.0 * cos(deg2rad($lat)));
        $meta_query[] = [
            'key'     => 'event_lat',
            'value'   => [ $lat - $lat_delta, $lat + $lat_delta ],
            'compare' => 'BETWEEN',
            'type'    => 'DECIMAL(10,6)',
        ];
        $meta_query[] = [
            'key'     => 'event_lng',
            'value'   => [ $lng - $lng_delta, $lng + $lng_delta ],
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

    $events = [];
    while ($query->have_posts()) {
        $query->the_post();
        $event_id = get_the_ID();
        $start    = get_post_meta($event_id, 'event_start_date', true);
        $end      = get_post_meta($event_id, 'event_end_date', true);
        $venue    = get_post_meta($event_id, 'venue_name', true);
        $lat_meta = get_post_meta($event_id, 'event_lat', true);
        $lng_meta = get_post_meta($event_id, 'event_lng', true);

        $events[] = [
            'id'    => $event_id,
            'title' => get_the_title(),
            'start' => $start,
            'end'   => $end,
            'url'   => get_permalink(),
            'venue' => $venue,
            'lat'   => $lat_meta,
            'lng'   => $lng_meta,
        ];
    }
    wp_reset_postdata();
    return $events;
}
