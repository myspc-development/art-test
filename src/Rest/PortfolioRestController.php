<?php

namespace ArtPulse\Rest;

class PortfolioRestController
{
    public static function register()
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/portfolio/(?P<user_id>\d+)')) {
            register_rest_route('artpulse/v1', '/portfolio/(?P<user_id>\d+)', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_portfolio'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
            'args'     => [
                'user_id' => [
                    'validate_callback' => 'is_numeric',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
        }
    }

    public static function get_portfolio($request)
    {
        $user_id = intval($request['user_id']);
        if (!$user_id) return new \WP_Error('invalid_user', 'Invalid user ID', ['status' => 400]);

        $items = get_posts([
            'post_type'   => 'portfolio',
            'author'      => $user_id,
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query'  => [[
                'key'   => 'portfolio_visibility',
                'value' => 'public',
            ]],
            // Fetch IDs only; no pagination so skip FOUND_ROWS.
            'fields'       => 'ids',
            'no_found_rows'=> true,
        ]);

        $response = [];
        foreach ($items as $post_id) {
            $response[] = [
                'id'          => $post_id,
                'title'       => get_post_field('post_title', $post_id),
                'description' => get_post_meta($post_id, 'portfolio_description', true),
                'link'        => get_post_meta($post_id, 'portfolio_link', true),
                'image'       => get_post_meta($post_id, 'portfolio_image', true),
                'category'    => wp_get_post_terms($post_id, 'portfolio_category', ['fields' => 'names'])[0] ?? '',
            ];
        }

        return rest_ensure_response($response);
    }
}
