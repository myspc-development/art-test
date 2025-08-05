<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\DashboardWidgetManager;

/**
 * Simple endpoints used by the React widget editor.
 */
class WidgetEditorController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/widgets')) {
            register_rest_route('artpulse/v1', '/widgets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_widgets'],
            'permission_callback' => fn () => current_user_can('read'),
        ]);
        }
        if (!ap_rest_route_registered('artpulse/v1', '/roles')) {
            register_rest_route('artpulse/v1', '/roles', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_roles'],
            'permission_callback' => fn () => current_user_can('manage_options'),
        ]);
        }
        if (!ap_rest_route_registered('artpulse/v1', '/layout/(?P<role>[a-z0-9_-]+)', 'GET')) {
            register_rest_route('artpulse/v1', '/layout/(?P<role>[a-z0-9_-]+)', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_layout'],
            'permission_callback' => fn () => current_user_can('manage_options'),
        ]);
        }
        if (!ap_rest_route_registered('artpulse/v1', '/layout/(?P<role>[a-z0-9_-]+)', 'POST')) {
            register_rest_route('artpulse/v1', '/layout/(?P<role>[a-z0-9_-]+)', [
            'methods'  => 'POST',
            'callback' => [self::class, 'save_layout'],
            'permission_callback' => fn () => current_user_can('manage_options'),
        ]);
        }
    }

    public static function get_widgets(): WP_REST_Response
    {
        $defs = DashboardWidgetManager::getWidgetDefinitions(true);
        return rest_ensure_response(array_values($defs));
    }

    public static function get_roles(): WP_REST_Response
    {
        global $wp_roles;
        $roles = array_keys($wp_roles->roles);
        return rest_ensure_response(array_values($roles));
    }

    public static function get_layout(WP_REST_Request $req): WP_REST_Response
    {
        $role = sanitize_key($req['role']);
        $result = DashboardWidgetManager::getRoleLayout($role);
        $layout = $result['layout'];
        $style  = \ArtPulse\Admin\UserLayoutManager::get_role_style($role);
        return rest_ensure_response([
            'layout' => $layout,
            'style'  => $style,
        ]);
    }

    public static function save_layout(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $role = sanitize_key($req['role']);
        $data = $req->get_json_params();
        $layout = $data['layout'] ?? [];
        if (!is_array($layout)) {
            return new WP_Error('invalid', 'Invalid layout', ['status' => 400]);
        }
        $style = isset($data['style']) && is_array($data['style']) ? $data['style'] : [];
        DashboardWidgetManager::saveRoleLayout($role, $layout);
        if ($style) {
            \ArtPulse\Admin\UserLayoutManager::save_role_style($role, $style);
        }
        return rest_ensure_response(['saved' => true]);
    }
}
