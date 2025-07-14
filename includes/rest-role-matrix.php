<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/roles/toggle', [
        'methods'  => 'POST',
        'callback' => function ($req) {
            if (!current_user_can('edit_users')) {
                return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
            }

            $user_id = absint($req['user_id']);
            $role    = sanitize_key($req['role']);
            $checked = filter_var($req['checked'], FILTER_VALIDATE_BOOLEAN);
            $user    = get_user_by('ID', $user_id);

            if (!$user) {
                return new WP_Error('notfound', 'User not found', ['status' => 404]);
            }

            if ($checked) {
                $user->add_role($role);
            } else {
                $user->remove_role($role);
            }

            return ['status' => 'ok', 'roles' => $user->roles];
        },
        'permission_callback' => '__return_true',
    ]);
});
