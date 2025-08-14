<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;

class DashboardConfigController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-config')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-config', [
                [
                    'methods'             => 'GET',
                    'callback'            => [self::class, 'get_config'],
                    'permission_callback' => fn() => current_user_can('read'),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [self::class, 'save_config'],
                    'permission_callback' => fn() => current_user_can('manage_options'),
                ],
            ]);
        }
    }

    public static function get_config(WP_REST_Request $request): WP_REST_Response {
        $visibility = OptionUtils::get_array_option('artpulse_widget_roles');
        $locked     = get_option('artpulse_locked_widgets', []);
        $role_map   = DashboardWidgetRegistry::get_role_widget_map();
        $role_widgets = [];
        foreach ($role_map as $role => $widgets) {
            $role_widgets[$role] = array_values(array_map(
                static fn($w) => sanitize_key($w['id'] ?? ''),
                $widgets
            ));
        }

        $defs        = DashboardWidgetRegistry::get_all();
        $capabilities = [];
        $excluded     = [];
        foreach ($defs as $id => $def) {
            if (!empty($def['capability'])) {
                $capabilities[$id] = sanitize_key($def['capability']);
            }
            if (!empty($def['exclude_roles'])) {
                $excluded[$id] = array_map('sanitize_key', (array) $def['exclude_roles']);
            }
        }

        return rest_ensure_response([
            'widget_roles' => $visibility,
            'role_widgets' => $role_widgets,
            'locked'       => array_values($locked),
            'capabilities' => $capabilities,
            'excluded_roles' => $excluded,
        ]);
    }

    public static function save_config(WP_REST_Request $request) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Invalid nonce', 'artpulse'), ['status' => 403]);
        }

        $data       = $request->get_json_params();
        $visibility = isset($data['widget_roles']) && is_array($data['widget_roles']) ? $data['widget_roles'] : [];
        $locked     = isset($data['locked']) && is_array($data['locked']) ? $data['locked'] : [];

        update_option('artpulse_widget_roles', $visibility);
        update_option('artpulse_locked_widgets', $locked);

        return rest_ensure_response(['saved' => true]);
    }
}

