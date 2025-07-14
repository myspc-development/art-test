<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SpotlightRestController
{
    private const NAMESPACE = 'artpulse/v1';

    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/spotlights', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_current'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ]);
    }

    public static function get_current(WP_REST_Request $request): WP_REST_Response
    {
        $today = current_time('Y-m-d');
        $query  = new \WP_Query([
            'post_type'      => 'artpulse_artist',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                [ 'key' => 'artist_spotlight', 'value' => '1' ],
                [
                    'key'     => 'spotlight_start_date',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'spotlight_end_date',
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                    [ 'key' => 'spotlight_end_date', 'compare' => 'NOT EXISTS' ],
                    [ 'key' => 'spotlight_end_date', 'value' => '', 'compare' => '=' ],
                ],
            ],
        ]);

        $data = [];
        foreach ($query->posts as $id) {
            $data[] = [
                'id'    => $id,
                'title' => get_the_title($id),
                'link'  => get_permalink($id),
                'thumb' => get_the_post_thumbnail_url($id, 'thumbnail'),
            ];
        }

        return rest_ensure_response($data);
    }
}
