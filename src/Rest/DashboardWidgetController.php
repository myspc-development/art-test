<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Use the dashboard builder registry rather than the core registry
// so we can query widgets and render previews for the builder UI.
use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

/**
 * REST controller for the Dashboard Builder.
 */
class DashboardWidgetController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/dashboard-widgets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        register_rest_route('artpulse/v1', '/dashboard-widgets/save', [
            'methods'  => 'POST',
            'callback' => [self::class, 'save_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
    }

    public static function check_permissions(WP_REST_Request $request): bool {
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce) {
            $_REQUEST['X-WP-Nonce'] = $nonce;
        }
        return current_user_can('manage_options') && wp_verify_nonce($nonce, 'wp_rest');
    }

    public static function get_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $role        = sanitize_key($request->get_param('role'));
        $include_all = filter_var($request->get_param('include_all'), FILTER_VALIDATE_BOOLEAN);
        if (!$role) {
            return new WP_Error('invalid_role', __('Role parameter missing', 'artpulse'), ['status' => 400]);
        }

        $available = array_values(DashboardWidgetRegistry::get_for_role($role));
        if (empty($available) && !get_role($role)) {
            error_log('Dashboard widgets request for unsupported role: ' . $role);
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        foreach ($available as &$widget) {
            $widget['preview'] = DashboardWidgetRegistry::render($widget['id']);
        }
        unset($widget);

        $active = get_option("artpulse_dashboard_widgets_{$role}");
        if (!is_array($active) || (empty($active['layout']) && empty($active['layoutOrder']))) {
            $preset_file = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . "data/preset-{$role}.json";
            if (file_exists($preset_file)) {
                $preset_json = @file_get_contents($preset_file);
                if ($preset_json === false) {
                    error_log('Failed reading preset file: ' . $preset_file);
                } else {
                    $preset_layout = json_decode($preset_json, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($preset_layout)) {
                        $active = [
                            'role'   => $role,
                            'layout' => $preset_layout,
                        ];
                    }
                }
            }
        }
        if (!is_array($active)) {
            $active = [
                'role' => $role,
                'enabledWidgets' => array_column($available, 'id'),
                'layoutOrder' => array_column($available, 'id'),
            ];
        }
        $response = [
            'available' => $available,
            'active'    => $active,
        ];

        if ($include_all) {
            $response['all'] = array_values(DashboardWidgetRegistry::get_all());
        }

        return rest_ensure_response($response);
    }

    public static function save_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        $role = sanitize_key($data['role'] ?? '');
        if (!$role) {
            return new WP_Error('invalid_role', __('Invalid role', 'artpulse'), ['status' => 400]);
        }
        if (empty(DashboardWidgetRegistry::get_for_role($role)) && !get_role($role)) {
            error_log('Attempt to save dashboard layout for unsupported role: ' . $role);
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        $enabled = array_map('sanitize_key', (array) ($data['enabledWidgets'] ?? []));
        $order   = array_map('sanitize_key', (array) ($data['layoutOrder'] ?? []));
        update_option("artpulse_dashboard_widgets_{$role}", [
            'role' => $role,
            'enabledWidgets' => $enabled,
            'layoutOrder' => $order,
        ]);
        return rest_ensure_response(['saved' => true]);
    }
}

