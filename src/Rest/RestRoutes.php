<?php

namespace ArtPulse\Rest;

use WP_REST_Request;

class RestRoutes
{
    public static function register()
    {
        add_action('rest_api_init', function () {
            // Register listing endpoints
            register_rest_route('artpulse/v1', '/events', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_events'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'city'   => [ 'type' => 'string', 'required' => false ],
                    'region' => [ 'type' => 'string', 'required' => false ],
                    'lat'    => [ 'type' => 'number', 'required' => false ],
                    'lng'    => [ 'type' => 'number', 'required' => false ],
                ],
            ]);

            register_rest_route('artpulse/v1', '/artists', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_artists'],
                'permission_callback' => '__return_true',
            ]);

            register_rest_route('artpulse/v1', '/artworks', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_artworks'],
                'permission_callback' => '__return_true',
            ]);

            register_rest_route('artpulse/v1', '/orgs', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_orgs'],
                'permission_callback' => '__return_true',
            ]);

            // ✅ Register the new SubmissionRestController endpoint
            \ArtPulse\Rest\SubmissionRestController::register();
            // Register favorites endpoint so frontend can toggle favorites
            \ArtPulse\Rest\FavoriteRestController::register();
            // Register import endpoint for CSV uploads
            \ArtPulse\Rest\ImportRestController::register();
            // Register template endpoints for CSV imports
            \ArtPulse\Rest\ImportTemplateController::register();
            // Register organization user management endpoints
            \ArtPulse\Rest\UserInvitationController::register();
        });

        $post_types = ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'];

        foreach ($post_types as $type) {
            add_action("save_post_{$type}", function () use ($type) {
                delete_transient('ap_rest_posts_' . $type);
            });
        }
    }

    public static function get_events(\WP_REST_Request $request)
    {
        $city   = sanitize_text_field($request->get_param('city'));
        $region = sanitize_text_field($request->get_param('region'));
        $lat    = $request->get_param('lat');
        $lng    = $request->get_param('lng');

        if ((null === $city || $city === '') || (null === $region || $region === '')) {
            if (is_numeric($lat) && is_numeric($lng)) {
                $nearest = self::nearest_city(floatval($lat), floatval($lng));
                if ($nearest) {
                    if (!$city) {
                        $city = $nearest['name'];
                    }
                    if (!$region) {
                        $region = $nearest['state'];
                    }
                }
            }
        }

        $args = [];
        $meta_query = [];

        if ($city) {
            $meta_query[] = [
                'key'   => 'event_city',
                'value' => $city,
            ];
        }

        if ($region) {
            $meta_query[] = [
                'key'   => 'event_state',
                'value' => $region,
            ];
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        return self::get_posts_with_meta('artpulse_event', [
            'event_date'     => '_ap_event_date',
            'event_location' => '_ap_event_location',
        ], $args);
    }

    public static function get_artists()
    {
        return self::get_posts_with_meta('artpulse_artist', [
            'artist_bio' => '_ap_artist_bio',
            'artist_org' => '_ap_artist_org',
        ]);
    }

    public static function get_artworks()
    {
        return self::get_posts_with_meta('artpulse_artwork', [
            'medium'     => '_ap_artwork_medium',
            'dimensions' => '_ap_artwork_dimensions',
            'materials'  => '_ap_artwork_materials',
        ]);
    }

    public static function get_orgs()
    {
        return self::get_posts_with_meta('artpulse_org', [
            'address' => 'ead_org_street_address',
            'website' => 'ead_org_website_url',
        ]);
    }

    private static function get_posts_with_meta($post_type, $meta_keys = [], array $query_args = [])
    {
        $transient_key = 'ap_rest_posts_' . $post_type;
        $use_cache     = empty($query_args);

        if ($use_cache) {
            $cached = get_transient($transient_key);
            if (false !== $cached) {
                return $cached;
            }
        }

        $posts  = get_posts(array_merge([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            // Fetch IDs only and skip FOUND_ROWS for a faster query.
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ], $query_args));

        $output = [];

        foreach ($posts as $post_id) {
            $item = [
                'id'      => $post_id,
                'title'   => get_the_title($post_id),
                'content' => apply_filters('the_content', get_post_field('post_content', $post_id)),
                'link'    => get_permalink($post_id),
            ];

            foreach ($meta_keys as $field => $meta_key) {
                $item[$field] = get_post_meta($post_id, $meta_key, true);
            }

            $output[] = $item;
        }

        if ($use_cache) {
            set_transient($transient_key, $output, HOUR_IN_SECONDS);
        }

        return $output;
    }

    private static function nearest_city(float $lat, float $lng): ?array
    {
        $cities = [
            ['name' => 'Los Angeles',   'state' => 'CA',  'lat' => 34.0522, 'lng' => -118.2437],
            ['name' => 'San Francisco', 'state' => 'CA',  'lat' => 37.7749, 'lng' => -122.4194],
            ['name' => 'New York City', 'state' => 'NY',  'lat' => 40.7128, 'lng' => -74.0060],
            ['name' => 'Buffalo',       'state' => 'NY',  'lat' => 42.8864, 'lng' => -78.8784],
            ['name' => 'Toronto',       'state' => 'ON',  'lat' => 43.6532, 'lng' => -79.3832],
            ['name' => 'Ottawa',        'state' => 'ON',  'lat' => 45.4215, 'lng' => -75.6972],
            ['name' => 'Montreal',      'state' => 'QC',  'lat' => 45.5019, 'lng' => -73.5674],
            ['name' => 'Quebec City',   'state' => 'QC',  'lat' => 46.8139, 'lng' => -71.2080],
            ['name' => 'London',        'state' => 'ENG', 'lat' => 51.5074, 'lng' => -0.1278],
            ['name' => 'Manchester',    'state' => 'ENG', 'lat' => 53.4808, 'lng' => -2.2426],
            ['name' => 'Edinburgh',     'state' => 'SCT', 'lat' => 55.9533, 'lng' => -3.1883],
            ['name' => 'Glasgow',       'state' => 'SCT', 'lat' => 55.8642, 'lng' => -4.2518],
            ['name' => 'Munich',        'state' => 'BY',  'lat' => 48.1351, 'lng' => 11.5820],
            ['name' => 'Berlin',        'state' => 'BE',  'lat' => 52.5200, 'lng' => 13.4050],
            ['name' => 'Paris',         'state' => 'IDF', 'lat' => 48.8566, 'lng' => 2.3522],
        ];

        $nearest = null;
        $min     = PHP_FLOAT_MAX;

        foreach ($cities as $city) {
            $dist = self::haversine_distance($lat, $lng, $city['lat'], $city['lng']);
            if ($dist < $min) {
                $min     = $dist;
                $nearest = $city;
            }
        }

        return $nearest;
    }

    private static function haversine_distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371; // km
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lng2 - $lng1);
        $a     = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c     = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    }
}
