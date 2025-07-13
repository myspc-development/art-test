<?php
namespace ArtPulse\Rest;

class DashboardPreviewController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/preview/dashboard', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_preview'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ]);
    }

    public static function get_preview() {
        return [
            'user'    => wp_get_current_user()->display_name,
            'widgets' => \ArtPulse\Admin\DashboardWidgetTools::get_role_widgets_for_current_user(),
        ];
    }
}
