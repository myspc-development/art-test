<?php
namespace ArtPulse\Rest;

use ArtPulse\Community\FavoritesManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class FavoriteRestController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/favorite', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'handle_request'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => self::get_schema(),
        ]);
    }

    public static function get_schema(): array
    {
        return [
            'object_id' => [
                'type'        => 'integer',
                'required'    => true,
                'description' => 'ID of the object to favorite or unfavorite.',
            ],
            'object_type' => [
                'type'        => 'string',
                'required'    => true,
                'description' => 'Type of the object.',
            ],
            'action' => [
                'type'        => 'string',
                'required'    => true,
                'enum'        => ['add', 'remove'],
                'description' => 'Whether to add or remove the favorite.',
            ],
        ];
    }

    public static function handle_request(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id    = get_current_user_id();
        $object_id  = absint($request['object_id']);
        $object_type = sanitize_key($request['object_type']);
        $action     = sanitize_text_field($request['action']);

        if (!$object_id || !$object_type) {
            return new WP_Error('invalid_params', 'Invalid parameters.', ['status' => 400]);
        }

        if ($action === 'add') {
            FavoritesManager::add_favorite($user_id, $object_id, $object_type);
            $status = 'added';
        } elseif ($action === 'remove') {
            FavoritesManager::remove_favorite($user_id, $object_id, $object_type);
            $status = 'removed';
        } else {
            return new WP_Error('invalid_action', 'Invalid action.', ['status' => 400]);
        }

        return rest_ensure_response([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
