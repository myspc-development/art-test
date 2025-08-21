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

        $meta_keys = [
            'event_start_date',
            'event_end_date',
            'venue_name',
            'event_street_address',
            'event_city',
            'event_state',
            'event_postcode',
            'event_country',
            'event_lat',
            'event_lng',
            'rsvp_enabled',
            'rsvp_limit',
            'waitlist_enabled',
        ];
        $meta = [];
        foreach ($meta_keys as $key) {
            $val = get_post_meta($event_id, $key, true);
            if ($val !== '') {
                $meta[$key] = $val;
            }
        }

        $org_id = intval(get_post_meta($event_id, '_ap_event_organization', true));
        $organization = [];
        if ($org_id) {
            $organization = [
                'name'         => get_the_title($org_id),
                'address'      => get_post_meta($org_id, 'ead_org_street_address', true),
                'website'      => get_post_meta($org_id, 'ead_org_website_url', true),
                'contact_name' => get_post_meta($org_id, 'ead_org_primary_contact_name', true),
                'contact_email'=> get_post_meta($org_id, 'ead_org_primary_contact_email', true),
                'contact_phone'=> get_post_meta($org_id, 'ead_org_primary_contact_phone', true),
                'contact_role' => get_post_meta($org_id, 'ead_org_primary_contact_role', true),
            ];
        }

        $event = [
            'id'                 => $event_id,
            'title'              => get_the_title(),
            'start'              => $meta['event_start_date'] ?? '',
            'end'                => $meta['event_end_date'] ?? '',
            'url'                => get_permalink(),
            'venue'              => $meta['venue_name'] ?? '',
            'lat'                => $meta['event_lat'] ?? '',
            'lng'                => $meta['event_lng'] ?? '',
            'event_organization' => $org_id,
            'organization'       => $organization,
            'meta'               => $meta,
        ];

        // Also expose meta fields at the top level for backward compatibility.
        foreach ($meta as $k => $v) {
            $event[$k] = $v;
        }

        $events[] = $event;
    }
    wp_reset_postdata();
    return $events;
}
