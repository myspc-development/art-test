<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

class RoleMatrixController
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
        register_rest_route(
            'artpulse/v1',
            '/roles/toggle',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [self::class, 'toggle_role'],
                    'permission_callback' => [self::class, 'can_manage_users'],
                    'args'                => [
                        'user_id' => ['type' => 'integer', 'required' => true],
                        'role'    => ['type' => 'string',  'required' => true],
                        'checked' => ['type' => 'boolean', 'required' => true],
                    ],
                ],
            ]
        );

        register_rest_route(
            'artpulse/v1',
            '/roles/batch',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [self::class, 'batch_update'],
                    'permission_callback' => [self::class, 'can_manage_users'],
                ],
            ]
        );

        register_rest_route(
            'artpulse/v1',
            '/roles/seed',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [self::class, 'seed'],
                    'permission_callback' => [self::class, 'can_manage_users'],
                ],
            ]
        );
    }

    public static function can_manage_users(): bool|WP_Error
    {
        if (current_user_can('edit_users')) {
            return true;
        }
        return new WP_Error('rest_forbidden', 'Forbidden', ['status' => 403]);
    }

    public static function toggle_role(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $user_id = absint($req->get_param('user_id'));
        $role    = sanitize_key($req->get_param('role'));
        $checked = rest_sanitize_boolean($req->get_param('checked'));
        $user    = get_user_by('ID', $user_id);

        if (!$user) {
            return new WP_Error('notfound', 'User not found', ['status' => 404]);
        }

        if ($checked) {
            $user->add_role($role);
        } else {
            $user->remove_role($role);
        }

        return rest_ensure_response(['status' => 'ok', 'roles' => $user->roles]);
    }

    public static function batch_update(WP_REST_Request $req): WP_REST_Response
    {
        $data = $req->get_json_params();
        if (!is_array($data)) {
            $data = [];
        }

        foreach ($data as $user_id => $map) {
            $user = get_user_by('ID', (int) $user_id);
            if (!$user) {
                continue;
            }
            foreach ($map as $role => $checked) {
                $role = sanitize_key($role);
                $checked = rest_sanitize_boolean($checked);
                if ($checked) {
                    $user->add_role($role);
                } else {
                    $user->remove_role($role);
                }
            }
        }

        return rest_ensure_response(['status' => 'ok']);
    }

    public static function seed(): WP_REST_Response
    {
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

        return rest_ensure_response(compact('users', 'roles', 'matrix'));
    }
}
