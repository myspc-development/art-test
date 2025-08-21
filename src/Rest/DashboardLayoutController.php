<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function ArtPulse\Rest\Util\require_login_and_cap;

/**
 * Simple user dashboard layout controller.
 */
class DashboardLayoutController {
    private const META_KEY = 'ap_dashboard_layout';

    public static function register(): void {
        $c = new self();
        add_action('rest_api_init', [$c, 'register_routes']);
    }

    public function register_routes(): void {
        $routes = [
            '/dashboard/layout',
            '/dashboard/widgets',
        ];
        foreach ($routes as $route) {
            register_rest_route(
                ARTPULSE_API_NAMESPACE,
                $route,
                [
                    [
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => [$this, 'get_layout'],
                        'permission_callback' => require_login_and_cap(),
                    ],
                    [
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => [$this, 'save_layout'],
                        'permission_callback' => require_login_and_cap(),
                        'args'                => [
                            'layout' => ['type' => 'array', 'required' => true],
                        ],
                    ],
                ]
            );
        }
    }

    public function get_layout(WP_REST_Request $req): WP_REST_Response {
        $uid    = get_current_user_id();
        $layout = get_user_meta($uid, self::META_KEY, true);
        $role   = $req->get_param('role');
        if (!$role) {
            $user = wp_get_current_user();
            $role = $user->roles[0] ?? 'member';
        } else {
            $role = sanitize_key($role);
        }

        if (!is_array($layout) || !$layout) {
            $defaults = $this->role_defaults($role);
            $vis      = array_fill_keys($defaults, true);
            return rest_ensure_response([
                'layout'     => $defaults,
                'visibility' => $vis,
            ]);
        }

        $layout_ids = [];
        $visibility = [];
        foreach ($layout as $item) {
            if (is_array($item)) {
                $id  = sanitize_key($item['id'] ?? '');
                $vis = isset($item['visible']) ? (bool)$item['visible'] : true;
            } else {
                $id  = sanitize_key((string)$item);
                $vis = true;
            }
            $id = preg_replace('/^widget_/', '', $id);
            if (!$id) {
                continue;
            }
            $layout_ids[] = $id;
            $visibility[$id] = $vis;
        }

        return rest_ensure_response([
            'layout'     => $layout_ids,
            'visibility' => $visibility,
        ]);
    }

    public function save_layout(WP_REST_Request $req): WP_REST_Response {
        $uid   = get_current_user_id();
        $items = (array) $req->get_param('layout');

        $map = [];
        foreach ($items as $item) {
            $id = '';
            $vis = true;
            if (is_array($item)) {
                $id = sanitize_title($item['id'] ?? '');
                $vis = isset($item['visible']) ? (bool)$item['visible'] : true;
            } else {
                $id = sanitize_title((string)$item);
            }
            $id = preg_replace('/^widget_/', '', $id);
            if ($id === '') {
                continue;
            }
            $map[$id] = ['id' => $id, 'visible' => $vis];
        }

        $clean = array_values($map);
        update_user_meta($uid, self::META_KEY, $clean);

        return rest_ensure_response($clean);
    }

    private function role_defaults(string $role): array {
        if ($role === 'member') {
            return ['membership', 'upgrade'];
        }
        return ['membership'];
    }
}
