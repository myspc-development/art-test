<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class OrgRolesController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_script']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/org-roles', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_roles'],
            'permission_callback' => '__return_true',
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
                'root'  => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'user_id' => get_current_user_id(),
            ]
        );
    }

    public static function get_roles(WP_REST_Request $request): WP_REST_Response {
        // TODO: Replace with real lookup.
        $roles = [
            ['id' => 1, 'name' => 'Curator'],
            ['id' => 2, 'name' => 'Artist'],
            ['id' => 3, 'name' => 'Patron'],
        ];
        return rest_ensure_response(['roles' => $roles]);
    }
}
