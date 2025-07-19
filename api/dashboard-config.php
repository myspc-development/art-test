<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/dashboard-config', [
        'methods'  => ['GET', 'POST'],
        'callback' => 'artpulse_handle_dashboard_config',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);
});

function artpulse_handle_dashboard_config(WP_REST_Request $request) {
    if ($request->get_method() === 'GET') {
        return ['widget_roles' => get_option('artpulse_widget_roles', [])];
    } else {
        $data = $request->get_json_params();
        $roles = isset($data['widget_roles']) ? (array) $data['widget_roles'] : [];
        update_option('artpulse_widget_roles', $roles);
        return ['success' => true];
    }
}

