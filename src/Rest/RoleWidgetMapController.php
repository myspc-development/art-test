<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use ArtPulse\Core\DashboardWidgetRegistry;

class RoleWidgetMapController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route(
            'artpulse/v1',
            '/role-widget-map',
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_map'],
                'permission_callback' => function () {
                    return current_user_can('read');
                },
            ]
        );
    }

    public static function get_map(): WP_REST_Response
    {
        $map = DashboardWidgetRegistry::get_role_widget_map();
        $out = [];
        foreach ($map as $role => $widgets) {
            $out[$role] = array_values(array_map(
                static fn($w) => sanitize_key($w['id'] ?? ''),
                is_array($widgets) ? $widgets : []
            ));
        }
        return rest_ensure_response($out);
    }
}
