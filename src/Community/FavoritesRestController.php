<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Community\FavoritesManager;

class FavoritesRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/favorites', [
            'methods'  => 'DELETE',
            'callback' => [self::class, 'remove_favorite'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'    => [
                'object_id' => [ 'type' => 'integer', 'required' => true ],
                'object_type' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
    }

    public static function remove_favorite(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id     = get_current_user_id();
        $object_id   = absint($request['object_id']);
        $object_type = sanitize_key($request['object_type']);

        if (!$object_id || !$object_type) {
            return new WP_Error('invalid_params', 'Invalid parameters.', ['status' => 400]);
        }

        FavoritesManager::remove_favorite($user_id, $object_id, $object_type);

        return rest_ensure_response(['status' => 'removed', 'id' => $object_id]);
    }
}
