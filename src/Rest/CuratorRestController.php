<?php
namespace ArtPulse\Rest;

use ArtPulse\Curator\CuratorManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CuratorRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/curators', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_curators'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ]);

        register_rest_route('artpulse/v1', '/curator/(?P<slug>[a-z0-9-]+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_curator'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
            'args'                => [
                'slug' => ['sanitize_callback' => 'sanitize_title'],
            ],
        ]);
    }

    public static function get_curators(WP_REST_Request $req): WP_REST_Response
    {
        $list = CuratorManager::get_all();
        return rest_ensure_response($list);
    }

    public static function get_curator(WP_REST_Request $req): WP_REST_Response
    {
        $slug = sanitize_title($req['slug']);
        $curator = CuratorManager::get_by_slug($slug);
        if (!$curator) {
            return new WP_REST_Response(['message' => 'Curator not found'], 404);
        }
        $collections = get_posts([
            'post_type'      => 'ap_collection',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'author'         => $curator['user_id'],
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        $curator['collections'] = array_map('intval', $collections);
        return rest_ensure_response($curator);
    }
}
