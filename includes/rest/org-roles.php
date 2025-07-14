<?php
if (!defined('ABSPATH')) { exit; }

use WP_REST_Request;
use ArtPulse\Core\OrgRoleManager;

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/org-roles', [
        'methods'             => 'GET',
        'callback'            => 'ap_get_org_roles',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);
});

function ap_get_org_roles(WP_REST_Request $request) {
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
