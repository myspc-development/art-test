<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

/**
 * REST controller for the Dashboard Builder.
 */
class DashboardWidgetsController {
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

    public static function get_widgets(WP_REST_Request $request): WP_REST_Response {
        $role = sanitize_key($request->get_param('role'));
        $available = array_values(DashboardWidgetRegistry::get_for_role($role));
        $active = get_option("artpulse_dashboard_widgets_{$role}", [
            'role' => $role,
            'enabledWidgets' => [],
            'layoutOrder' => [],
        ]);
        return rest_ensure_response([
            'available' => $available,
            'active'    => $active,
        ]);
    }

    public static function save_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        $role = sanitize_key($data['role'] ?? '');
        if (!$role) {
            return new WP_Error('invalid_role', __('Invalid role', 'artpulse'), ['status' => 400]);
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
