<?php
namespace ArtPulse\Rest;

class DashboardMessagesController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/dashboard/messages', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_messages'],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
    }

    public static function get_messages() {
        return [ ['id' => 1, 'content' => 'Sample message'] ];
    }
}
