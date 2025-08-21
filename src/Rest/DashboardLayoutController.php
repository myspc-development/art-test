<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

/**
 * Layout persistence + defaults with filter hooks used in tests.
 */
final class DashboardLayoutController {
    protected const NS = 'ap/v1';

    public static function register(): void {
        add_action('rest_api_init', [self::class, 'routes']);
    }

    public static function routes(): void {
        // Primary
        register_rest_route(self::NS, '/dashboard/layout', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_layout'],
            'permission_callback' => Auth::require_login_and_cap('read'),
            'args'                => [
                'role' => ['type' => 'string'],
            ],
        ]);
        register_rest_route(self::NS, '/dashboard/layout', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [self::class, 'save_layout'],
            'permission_callback' => Auth::require_login_and_cap('read'),
        ]);

        // Alias routes used by tests
        register_rest_route(self::NS, '/dashboard/layout/alias', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [self::class, 'get_layout'],
            'permission_callback' => Auth::require_login_and_cap('read'),
        ]);
        register_rest_route(self::NS, '/dashboard/layout/alias', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [self::class, 'save_layout'],
            'permission_callback' => Auth::require_login_and_cap('read'),
        ]);
    }

    public static function get_layout(WP_REST_Request $req): WP_REST_Response {
        $role       = (string) ($req->get_param('role') ?? '');
        $user_id    = get_current_user_id();
        $meta_key   = 'ap_dashboard_layout';

        // User layout first
        $saved = get_user_meta($user_id, $meta_key, true);
        if (is_array($saved) && $saved) {
            $ids = array_values(array_unique(array_map([self::class,'slug'], array_column($saved, 'id'))));
            return new WP_REST_Response($ids, 200);
        }

        // Role default via filter used by tests
        $default = apply_filters('ap_dashboard_default_layout', [], $role);
        $ids     = array_values(array_unique(array_map([self::class,'slug'], (array) $default)));

        return new WP_REST_Response($ids, 200);
    }

    public static function save_layout(WP_REST_Request $req): WP_REST_Response {
        $user_id  = get_current_user_id();
        $meta_key = 'ap_dashboard_layout';

        $body = $req->get_json_params();
        $items = is_array($body) ? $body : ($body['layout'] ?? []);
        if (!is_array($items)) $items = [];

        // Allowed widget whitelist via filter (tests supply very small sets)
        $allowed = apply_filters('ap_dashboard_widget_whitelist', []);
        $allowed = array_map([self::class,'slug'], (array) $allowed);
        $allowed_set = array_flip($allowed);

        $seen = [];
        $clean = [];
        foreach ($items as $row) {
            $id = self::slug($row['id'] ?? '');
            if (!$id) continue;
            // Drop unknown widgets silently (tests expect ignore, not 400)
            if ($allowed && !isset($allowed_set[$id])) continue;
            if (isset($seen[$id])) continue;
            $seen[$id] = true;
            $clean[] = [
                'id' => $id,
                'visible' => (bool) ($row['visible'] ?? true),
            ];
        }

        update_user_meta($user_id, $meta_key, $clean);
        return new WP_REST_Response($clean, 200);
    }

    private static function slug(string $s): string {
        $s = strtolower($s);
        // keep tests happy: allow bare ids like 'a', 'one', etc.
        return preg_replace('/[^a-z0-9_\-]/', '', $s);
    }
}
