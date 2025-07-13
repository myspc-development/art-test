<?php
namespace ArtPulse\Rest;

class CurrentUserController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/me', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_current_user'],
            'permission_callback' => fn() => current_user_can('read'),
        ]);
    }

    public static function get_current_user() {
        $user = wp_get_current_user();
        return [
            'id' => $user->ID,
            'role' => $user->roles[0] ?? 'member',
        ];
    }
}
