<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventMapController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/event-map', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_locations'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function get_locations(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $posts = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);

        $events = [];
        foreach ($posts as $event_id) {
            $lat = get_post_meta($event_id, 'event_latitude', true);
            $lng = get_post_meta($event_id, 'event_longitude', true);
            if (!$lat || !$lng) {
                $loc_parts = [];
                $location = get_post_meta($event_id, '_ap_event_location', true);
                $address = get_post_meta($event_id, 'event_street_address', true);
                $city    = get_post_meta($event_id, 'event_city', true);
                $state   = get_post_meta($event_id, 'event_state', true);
                $country = get_post_meta($event_id, 'event_country', true);
                if ($location) $loc_parts[] = $location;
                if ($address)  $loc_parts[] = $address;
                if ($city)     $loc_parts[] = $city;
                if ($state)    $loc_parts[] = $state;
                if ($country)  $loc_parts[] = $country;
                $query = implode(', ', array_filter($loc_parts));
                if ($query) {
                    $coords = self::geocode($query);
                    if ($coords) {
                        [$lat, $lng] = $coords;
                        update_post_meta($event_id, 'event_latitude', $lat);
                        update_post_meta($event_id, 'event_longitude', $lng);
                    }
                }
            }
            if ($lat && $lng) {
                $events[] = [
                    'id'    => $event_id,
                    'title' => get_the_title($event_id),
                    'lat'   => (float) $lat,
                    'lng'   => (float) $lng,
                    'link'  => get_permalink($event_id),
                ];
            }
        }

        return rest_ensure_response($events);
    }

    private static function geocode(string $address): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);
        $resp = wp_remote_get($url, ['timeout' => 10, 'user-agent' => 'ArtPulseMap/1.0']);
        if (is_wp_error($resp)) {
            return null;
        }
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (!empty($body[0]['lat']) && !empty($body[0]['lon'])) {
            return [floatval($body[0]['lat']), floatval($body[0]['lon'])];
        }
        return null;
    }
}
