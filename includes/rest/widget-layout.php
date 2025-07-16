<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/widget-layout', [
        'methods'  => 'POST',
        'callback' => function ($request) {
            $layout = $request->get_json_params();
            if (!is_array($layout)) {
                $layout = [];
            }
            update_user_meta(get_current_user_id(), 'artpulse_dashboard_layout', $layout);
            return rest_ensure_response(['saved' => true]);
        },
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);
});
