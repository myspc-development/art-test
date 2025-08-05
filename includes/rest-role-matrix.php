<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    if (!ap_rest_route_registered('artpulse/v1', '/roles/toggle')) {
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
        'permission_callback' => function () {
            if (current_user_can('edit_users')) {
                return true;
            }
            return new WP_Error('rest_forbidden', 'Forbidden', ['status' => 403]);
        },
    ]);
    }

    if (!ap_rest_route_registered('artpulse/v1', '/roles/batch')) {
        register_rest_route('artpulse/v1', '/roles/batch', [
        'methods'  => 'POST',
        'callback' => function (WP_REST_Request $req) {
            if (!current_user_can('edit_users')) {
                return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
            }

            $data = $req->get_json_params(); // { user_id: { role: boolean } }

            foreach ($data as $user_id => $map) {
                $user = get_user_by('ID', (int) $user_id);
                if (!$user) {
                    continue;
                }
                foreach ($map as $role => $checked) {
                    if ($checked) {
                        $user->add_role(sanitize_key($role));
                    } else {
                        $user->remove_role(sanitize_key($role));
                    }
                }
            }

            return ['status' => 'ok'];
        },
        'permission_callback' => function () {
            if (current_user_can('edit_users')) {
                return true;
            }
            return new WP_Error('rest_forbidden', 'Forbidden', ['status' => 403]);
        },
    ]);
    }

    if (!ap_rest_route_registered('artpulse/v1', '/roles/seed')) {
        register_rest_route('artpulse/v1', '/roles/seed', [
        'methods'  => 'GET',
        'callback' => function () {
            if (!current_user_can('edit_users')) {
                return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
            }

            $users = array_map(
                function ($u) {
                    return [
                        'ID'           => $u->ID,
                        'display_name' => $u->display_name,
                    ];
                },
                get_users()
            );

            $roles = [];
            foreach (wp_roles()->roles as $key => $r) {
                $roles[] = [
                    'key'  => $key,
                    'name' => $r['name'],
                    'caps' => array_keys($r['capabilities']),
                ];
            }

            $matrix = [];
            foreach (get_users() as $u) {
                foreach ($roles as $r) {
                    $matrix[$u->ID][$r['key']] = in_array($r['key'], $u->roles, true);
                }
            }

            return compact('users', 'roles', 'matrix');
        },
        'permission_callback' => function () {
            if (current_user_can('edit_users')) {
                return true;
            }
            return new WP_Error('rest_forbidden', 'Forbidden', ['status' => 403]);
        },
    ]);
    }
});
