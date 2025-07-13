<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User_Query;

class DirectoryController
{
    public static function register_routes(): void
    {
        register_rest_route('art/v1', '/artists', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_artists'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('art/v1', '/events', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_events'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function get_artists(WP_REST_Request $request): WP_REST_Response
    {
        $query  = new WP_User_Query([
            'role'   => 'artist',
            'fields' => ['ID', 'display_name'],
            'number' => -1,
        ]);
        $data = [];
        foreach ($query->get_results() as $u) {
            $data[] = [
                'id'   => $u->ID,
                'name' => $u->display_name,
                'link' => get_author_posts_url($u->ID),
            ];
        }
        return rest_ensure_response($data);
    }

    public static function get_events(WP_REST_Request $request): WP_REST_Response
    {
        $query = new \WP_Query([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'meta_key'       => 'event_start_date',
            'order'          => 'ASC',
        ]);
        $data = [];
        foreach ($query->posts as $post) {
            $data[] = [
                'id'         => $post->ID,
                'title'      => $post->post_title,
                'link'       => get_permalink($post),
                'start_date' => get_post_meta($post->ID, 'event_start_date', true),
                'end_date'   => get_post_meta($post->ID, 'event_end_date', true),
            ];
        }
        return rest_ensure_response($data);
    }
}
