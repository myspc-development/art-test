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
            'callback'            => [self::class, 'get_roles'],
            'permission_callback' => static function () {
                return current_user_can('read');
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
                'api_url' => rest_url('artpulse/v1/org-roles'),
                'nonce'   => wp_create_nonce('wp_rest'),
            ]
        );
    }

    public static function get_roles(WP_REST_Request $request): WP_REST_Response {
        $org_id = absint($request->get_param('org_id'));
        if (!$org_id) {
            $user_id = get_current_user_id();
            $org_id  = intval(get_user_meta($user_id, 'ap_organization_id', true));
        }

        $roles = OrgRoleManager::get_roles($org_id);

        return rest_ensure_response(['roles' => $roles]);
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

        $roles = OrgRoleManager::get_roles($org_id);

        wp_send_json_success(['roles' => $roles]);
    }
}
