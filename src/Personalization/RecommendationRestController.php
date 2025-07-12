<?php
namespace ArtPulse\Personalization;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RecommendationRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/recommendations', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_recommendations'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => [
                'type' => [
                    'type'    => 'string',
                    'enum'    => ['event', 'artist'],
                    'default' => 'event',
                ],
                'user_id' => [
                    'type'    => 'integer',
                    'required'=> false,
                ],
                'limit' => [
                    'type'    => 'integer',
                    'default' => 6,
                ],
                'location' => [
                    'type'     => 'string',
                    'required' => false,
                    'description' => 'ZIP code or "lat,lng" pair',
                ],
            ],
        ]);
    }

    public static function get_recommendations(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id = $request->get_param('user_id');
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return new WP_Error('invalid_user', 'User not specified', ['status' => 400]);
        }

        $type     = sanitize_key($request->get_param('type'));
        $limit    = absint($request->get_param('limit'));
        $location = $request->get_param('location');
        $data  = RecommendationEngine::get_recommendations((int)$user_id, $type, $limit, $location);
        return rest_ensure_response($data);
    }
}
