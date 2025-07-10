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
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
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

    private static function adjust_favorite_count(int $post_id, int $delta): void
    {
        if (!get_post($post_id)) {
            return;
        }

        global $wpdb;
        $meta_key = 'ap_favorite_count';

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} SET meta_value = GREATEST(CAST(meta_value AS SIGNED) + %d, 0) WHERE post_id = %d AND meta_key = %s",
                $delta,
                $post_id,
                $meta_key
            )
        );

        if (!$updated) {
            $value = max(0, $delta);
            add_post_meta($post_id, $meta_key, $value, true);
        }
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
            self::adjust_favorite_count($object_id, 1);
            $status = 'added';
        } elseif ($action === 'remove') {
            FavoritesManager::remove_favorite($user_id, $object_id, $object_type);
            self::adjust_favorite_count($object_id, -1);
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
