<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Core\UserDashboardManager;

/**
 * Simplified REST controller for user dashboard layouts.
 */
class DashboardLayoutRestController
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
        if (!ap_rest_route_registered('artpulse/v1', '/dashboard/layout')) {
            register_rest_route('artpulse/v1', '/dashboard/layout', [
                [
                    'methods'             => 'GET',
                    'callback'            => [self::class, 'get_layout'],
                    'permission_callback' => fn() => current_user_can('read'),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [self::class, 'save_layout'],
                    'permission_callback' => fn() => current_user_can('read'),
                    'args'                => [
                        'layout'     => ['type' => 'array', 'required' => false],
                        'visibility' => ['type' => 'object', 'required' => false],
                    ],
                ],
            ]);
        }
    }

    public static function get_layout(WP_REST_Request $request): WP_REST_Response
    {
        return UserDashboardManager::getDashboardLayout();
    }

    public static function save_layout(WP_REST_Request $request): WP_REST_Response
    {
        return UserDashboardManager::saveDashboardLayout($request);
    }
}
