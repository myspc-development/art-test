<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Community\NotificationManager;

class NotificationRestController
{
    public static function register(): void
    {
        self::register_routes();
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/notifications', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'list'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/notifications/(?P<id>\\d+)/read', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'mark_read'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/notifications/mark-all-read', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'mark_all_read'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function list(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $limit   = isset($request['limit']) ? absint($request['limit']) : 25;

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
}
