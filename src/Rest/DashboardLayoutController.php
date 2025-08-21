<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

/**
 * REST controller for managing dashboard layouts.
 */
final class DashboardLayoutController extends WP_REST_Controller {
    /** @var string */
    protected $namespace = 'ap/v1';

    /**
     * Register the controller.
     */
    public static function register(): void {
        $controller = new self();
        add_action('rest_api_init', [ $controller, 'register_routes' ]);
    }

    /**
     * Register REST API routes.
     */
    public function register_routes(): void {
        register_rest_route($this->namespace, '/dashboard/layout', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_layout' ],
                'permission_callback' => Auth::require_login_and_cap('read'),
                'args'                => [
                    'role' => [ 'type' => 'string', 'required' => false ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_layout' ],
                'permission_callback' => Auth::require_login_and_cap('read'),
            ],
        ]);

        register_rest_route($this->namespace, '/dashboard/layout/alias/(?P<alias>[a-z0-9_-]+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_alias' ],
            'permission_callback' => Auth::require_login_and_cap('read'),
        ]);
    }

    /**
     * Sanitize a widget ID.
     */
    private static function sanitize_id(string $id): string {
        $id = strtolower($id);
        return preg_replace('/[^a-z0-9_\-]/', '', $id);
    }

    /**
     * Get the current dashboard layout.
     */
    public function get_layout(WP_REST_Request $request): WP_REST_Response {
        $user_id = get_current_user_id();
        $role    = sanitize_key($request->get_param('role') ?? '');
        if (!$role) {
            $user = wp_get_current_user();
            $role = $user->roles[0] ?? '';
        }

        $saved = get_user_meta($user_id, 'ap_dashboard_layout', true);
        $items = is_array($saved) ? $saved : [];

        if (empty($items)) {
            $defaults = (array) apply_filters('ap_dashboard_default_widgets', []);
            if ($role) {
                $defaults = (array) apply_filters('ap_dashboard_default_widgets_for_role', $defaults, $role);
            }
            $items = array_map(fn($id) => [ 'id' => $id, 'visible' => true ], $defaults);
        }

        $layout_ids = [];
        $visibility = [];
        $seen       = [];
        foreach ($items as $row) {
            $id = self::sanitize_id($row['id'] ?? '');
            if (!$id || isset($seen[$id])) {
                continue;
            }
            $seen[$id]   = true;
            $visible     = isset($row['visible']) ? (bool) $row['visible'] : true;
            $layout_ids[] = $id;
            $visibility[] = [ 'id' => $id, 'visible' => $visible ];
        }

        return rest_ensure_response([
            'layout'     => $layout_ids,
            'visibility' => $visibility,
        ]);
    }

    /**
     * Save the dashboard layout.
     */
    public function save_layout(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', 'Invalid nonce.', [ 'status' => 401 ]);
        }

        $items = $request->get_param('layout');
        if (!is_array($items)) {
            $items = [];
        }

        $clean      = [];
        $layout_ids = [];
        $seen       = [];
        foreach ($items as $row) {
            $id = self::sanitize_id($row['id'] ?? '');
            if (!$id || isset($seen[$id])) {
                continue;
            }
            $seen[$id]   = true;
            $visible      = isset($row['visible']) ? (bool) $row['visible'] : true;
            $layout_ids[] = $id;
            $clean[]      = [ 'id' => $id, 'visible' => $visible ];
        }

        update_user_meta(get_current_user_id(), 'ap_dashboard_layout', $clean);

        return rest_ensure_response([
            'layout'     => $layout_ids,
            'visibility' => $clean,
        ]);
    }

    /**
     * Return layout for a predefined alias.
     */
    public function get_alias(WP_REST_Request $request): WP_REST_Response {
        $alias = self::sanitize_id($request['alias'] ?? '');
        $map   = (array) apply_filters('ap_dashboard_alias_map', []);
        $layout = isset($map[$alias]) ? (array) $map[$alias] : [];

        $layout = array_values(array_map([self::class, 'sanitize_id'], $layout));
        $visibility = array_map(fn($id) => [ 'id' => $id, 'visible' => true ], $layout);

        return rest_ensure_response([
            'layout'     => $layout,
            'visibility' => $visibility,
        ]);
    }
}
