<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Core\OrgRoleManager;

class OrgRolesController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_script']);
        add_action('wp_ajax_ap_get_org_roles', [self::class, 'ajax_get_roles']);
        add_action('wp_ajax_nopriv_ap_get_org_roles', [self::class, 'ajax_get_roles']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/org-roles', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_roles_for_org'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);

        register_rest_route('artpulse/v1', '/org-roles/update', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'update_roles'],
            'permission_callback' => [self::class, 'can_manage_roles'],
        ]);

        register_rest_route('artpulse/v1', '/org-roles/users', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_users_with_roles'],
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
        ]);

        register_rest_route('artpulse/v1', '/org-roles/assign', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'assign_roles_to_user'],
            'permission_callback' => static function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    public static function enqueue_script(): void {
        wp_enqueue_script(
            'ap-org-roles',
            plugins_url('assets/js/ap-org-roles.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );

        wp_localize_script(
            'ap-org-roles',
            'ArtPulseOrgRoles',
            [
                'api_path' => 'artpulse/v1/org-roles',
                'nonce'    => wp_create_nonce('wp_rest'),
            ]
        );
    }

    public static function get_roles(WP_REST_Request $request): WP_REST_Response {
        $org_id = absint($request->get_param('org_id'));
        if (!$org_id) {
            $user_id = get_current_user_id();
            $org_id  = intval(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $roles  = OrgRoleManager::get_roles($org_id);
        $result = [];
        foreach ($roles as $key => $r) {
            $label = $r['name'] ?? $key;
            $desc  = $r['description'] ?? '';
            $count = count(get_users([
                'meta_key'   => 'ap_org_roles',
                'meta_value' => $key,
                'fields'     => 'ID',
            ]));
            if (!$count) {
                $count = count(get_users([
                    'meta_key'   => 'ap_org_role',
                    'meta_value' => $key,
                    'fields'     => 'ID',
                ]));
            }
            $result[] = [
                'key'         => $key,
                'label'       => $label,
                'description' => $desc,
                'user_count'  => $count,
            ];
        }

        return rest_ensure_response($result);
    }

    /**
     * AJAX callback for retrieving organization roles.
     */
    public static function ajax_get_roles(): void {
        check_ajax_referer('ap_org_roles_nonce', 'nonce');

        $org_id = absint($_POST['org_id'] ?? 0);
        if (!$org_id) {
            $user_id = get_current_user_id();
            $org_id  = intval(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $roles  = OrgRoleManager::get_roles($org_id);
        $result = [];
        foreach ($roles as $key => $r) {
            $label = $r['name'] ?? $key;
            $desc  = $r['description'] ?? '';
            $count = count(get_users([
                'meta_key'   => 'ap_org_roles',
                'meta_value' => $key,
                'fields'     => 'ID',
            ]));
            if (!$count) {
                $count = count(get_users([
                    'meta_key'   => 'ap_org_role',
                    'meta_value' => $key,
                    'fields'     => 'ID',
                ]));
            }
            $result[] = [
                'key'         => $key,
                'label'       => $label,
                'description' => $desc,
                'user_count'  => $count,
            ];
        }

        wp_send_json_success($result);
    }

    public static function get_users_with_roles(WP_REST_Request $request): WP_REST_Response {
        $org_id = absint($request->get_param('org_id'));
        if (!$org_id) {
            $user_id = get_current_user_id();
            $org_id  = intval(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $users = get_users([
            'meta_key'   => 'ap_organization_id',
            'meta_value' => $org_id,
        ]);

        $data = [];
        foreach ($users as $u) {
            $data[] = [
                'ID'           => $u->ID,
                'display_name' => $u->display_name,
                'roles'        => OrgRoleManager::get_user_roles($u->ID),
            ];
        }

        return rest_ensure_response($data);
    }

    public static function assign_roles_to_user(WP_REST_Request $request): WP_REST_Response {
        $user_id = absint($request['user_id']);
        $roles   = array_map('sanitize_key', (array) $request['roles']);

        if (!$user_id || empty($roles)) {
            return new WP_REST_Response(['error' => 'Invalid user or roles'], 400);
        }

        OrgRoleManager::assign_roles($user_id, $roles);

        return new WP_REST_Response(['success' => true]);
    }

    public static function check_permissions(): bool
    {
        return current_user_can('manage_org_roles');
    }

    public static function can_manage_roles(WP_REST_Request $request): bool
    {
        return current_user_can('manage_org_roles') &&
               check_ajax_referer('wp_rest', '_wpnonce', false);
    }

    public static function get_roles_for_org(WP_REST_Request $request): WP_REST_Response
    {
        $org_id = absint($request->get_param('org_id'));
        if (!$org_id) {
            $user_id = get_current_user_id();
            $org_id  = intval(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $roles = [];
        foreach (OrgRoleManager::get_roles($org_id) as $slug => $info) {
            $roles[] = [
                'slug' => $slug,
                'name' => $info['name'] ?? $slug,
            ];
        }

        $users = [];
        $members = get_users([
            'meta_key'   => 'ap_organization_id',
            'meta_value' => $org_id,
        ]);
        foreach ($members as $u) {
            $users[] = [
                'id'   => $u->ID,
                'name' => $u->display_name,
                'role' => OrgRoleManager::get_user_org_role($u->ID, $org_id),
            ];
        }

        return rest_ensure_response([
            'org_id' => $org_id,
            'roles'  => $roles,
            'users'  => $users,
        ]);
    }

    public static function update_roles(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        $org_id = absint($params['org_id'] ?? 0);
        $map    = $params['roles'] ?? [];

        if (!$org_id || !is_array($map)) {
            return new WP_REST_Response(['error' => 'invalid'], 400);
        }

        foreach ($map as $user => $role) {
            $uid = absint($user);
            if ($uid) {
                OrgRoleManager::assign_roles($uid, [sanitize_key($role)]);
            }
        }

        return rest_ensure_response(['success' => true]);
    }
}
