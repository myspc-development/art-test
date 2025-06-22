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
}
