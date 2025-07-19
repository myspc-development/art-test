<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class DashboardConfigController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/dashboard-config', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_config'],
            'permission_callback' => fn() => current_user_can('read'),
        ]);
        register_rest_route('artpulse/v1', '/dashboard-config', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'save_config'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);
    }

    public static function get_config(WP_REST_Request $request): WP_REST_Response {
        $roles  = get_option('artpulse_widget_roles', []);
        $locked = get_option('artpulse_locked_widgets', []);

        return rest_ensure_response([
            'widget_roles' => $roles,
            'locked'       => array_values($locked),
        ]);
    }

    public static function save_config(WP_REST_Request $request) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Invalid nonce', 'artpulse'), ['status' => 403]);
        }

        $data   = $request->get_json_params();
        $roles  = isset($data['widget_roles']) && is_array($data['widget_roles']) ? $data['widget_roles'] : [];
        $locked = isset($data['locked']) && is_array($data['locked']) ? $data['locked'] : [];

        update_option('artpulse_widget_roles', $roles);
        update_option('artpulse_locked_widgets', $locked);

        return rest_ensure_response(['saved' => true]);
    }
}

