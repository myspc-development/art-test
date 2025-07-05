<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;

class LeaderboardRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/leaderboards/most-helpful', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'most_helpful'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('artpulse/v1', '/leaderboards/most-upvoted', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'most_upvoted'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function most_helpful(WP_REST_Request $req): WP_REST_Response
    {
        $limit = $req->get_param('limit') ? absint($req['limit']) : 5;
        $data  = LeaderboardManager::get_most_helpful($limit);
        return rest_ensure_response($data);
    }

    public static function most_upvoted(WP_REST_Request $req): WP_REST_Response
    {
        $limit = $req->get_param('limit') ? absint($req['limit']) : 5;
        $data  = LeaderboardManager::get_most_upvoted($limit);
        return rest_ensure_response($data);
    }
}
