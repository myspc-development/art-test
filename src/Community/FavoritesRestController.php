<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Community\NotificationManager;
use ArtPulse\Community\FavoritesManager;

class FavoritesRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        // Existing routes
        register_rest_route('artpulse/v1', '/notifications', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_notifications'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/notifications/read', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'mark_as_read'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => self::get_schema(),
        ]);

        register_rest_route('artpulse/v1', '/notifications', [
            'methods'             => 'DELETE',
            'callback'            => [self::class, 'delete_notification'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => self::get_schema(),
        ]);

        // New routes
        register_rest_route('artpulse/v1', '/notifications', [
            'methods'  => 'GET',
            'callback' => [self::class, 'list'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/notifications/(?P<id>\d+)/read', [
            'methods'  => 'POST',
            'callback' => [self::class, 'mark_read'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/notifications/mark-all-read', [
            'methods'  => 'POST',
            'callback' => [self::class, 'mark_all_read'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

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

    public static function get_schema(): array
    {
        return [
            'notification_id' => [
                'type'        => 'integer',
                'required'    => true,
                'description' => 'ID of the notification to update or delete.',
            ],
        ];
    }

    public static function get_notifications(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $notifications = get_user_meta($user_id, '_ap_notifications', true) ?: [];

        return rest_ensure_response([
            'notifications' => array_values($notifications)
        ]);
    }

    public static function mark_as_read(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();
        $id = absint($request['notification_id']);
        $notifications = get_user_meta($user_id, '_ap_notifications', true) ?: [];

        foreach ($notifications as &$n) {
            if ((int) $n['id'] === $id) {
                $n['read'] = true;
            }
        }

        update_user_meta($user_id, '_ap_notifications', $notifications);

        return rest_ensure_response(['status' => 'read', 'id' => $id]);
    }

    public static function delete_notification(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();
        $id = absint($request['notification_id']);
        $notifications = get_user_meta($user_id, '_ap_notifications', true) ?: [];

        $filtered = array_filter($notifications, fn($n) => (int) $n['id'] !== $id);
        update_user_meta($user_id, '_ap_notifications', array_values($filtered));

        return rest_ensure_response(['status' => 'deleted', 'id' => $id]);
    }

    // Notification routes using the new NotificationManager table
    public static function list(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $limit    = isset($request['limit']) ? absint($request['limit']) : 25;

        $notifications = NotificationManager::get($user_id, $limit);

        return rest_ensure_response($notifications);
    }

    public static function mark_read(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $id      = absint($request['id']);

        if ($id) {
            NotificationManager::mark_read($id, $user_id);
        }

        return rest_ensure_response(['status' => 'read', 'id' => $id]);
    }

    public static function mark_all_read(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        NotificationManager::mark_all_read($user_id);

        return rest_ensure_response(['status' => 'all_read']);
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
