<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Use the dashboard builder registry rather than the core registry
// so we can query widgets and render previews for the builder UI.
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * REST controller for the Dashboard Builder.
 */
class DashboardWidgetController {

    /**
     * Convert an array of builder layout items to core widget IDs.
     * Logs a warning if a widget ID cannot be mapped.
     *
     * @param array<int,array|string> $layout
     * @return array<int,array>
     */
    private static function convert_to_core_ids(array $layout): array {
        $converted = [];
        foreach ($layout as $item) {
            if (is_array($item)) {
                $id  = sanitize_key($item['id'] ?? '');
                $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
            } else {
                $id  = sanitize_key((string) $item);
                $vis = true;
            }

            $core = self::to_core_id($id);
            if ($id && $core === $id && !\ArtPulse\Core\DashboardWidgetRegistry::get($id)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[DashboardBuilder] Unmapped widget ID: ' . $id);
                }
            }

            $converted[] = [
                'id'      => $core,
                'visible' => $vis,
            ];
        }

        return $converted;
    }

    /**
     * Convert an array of core layout items to builder widget IDs.
     *
     * @param array<int,array|string> $layout
     * @return array<int,array>
     */
    private static function convert_to_builder_ids(array $layout): array {
        $converted = [];
        foreach ($layout as $item) {
            if (is_array($item)) {
                $id  = sanitize_key($item['id'] ?? '');
                $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
            } else {
                $id  = sanitize_key((string) $item);
                $vis = true;
            }

            $converted[] = [
                'id'      => self::to_builder_id($id),
                'visible' => $vis,
            ];
        }

        return $converted;
    }

    /**
     * Convert a builder widget ID to the core ID.
     */
    private static function to_core_id(string $id): string {
        return \ArtPulse\Core\DashboardWidgetRegistry::map_to_core_id($id);
    }

    /**
     * Convert a core widget ID to the builder ID.
     */
    private static function to_builder_id(string $id): string {
        return \ArtPulse\Core\DashboardWidgetRegistry::map_to_builder_id($id);
    }
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-widgets')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-widgets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        }
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/save')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/save', [
            'methods'  => 'POST',
            'callback' => [self::class, 'save_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        }
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/export')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/export', [
            'methods'  => 'GET',
            'callback' => [self::class, 'export_layout'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        }
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/import')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-widgets/import', [
            'methods'  => 'POST',
            'callback' => [self::class, 'import_layout'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        }
    }

    public static function check_permissions(WP_REST_Request $request): bool {
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce) {
            $_REQUEST['X-WP-Nonce'] = $nonce;
        }
        return current_user_can('manage_options') && wp_verify_nonce($nonce, 'wp_rest');
    }

    public static function get_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $role        = sanitize_key($request->get_param('role'));
        $include_all = filter_var($request->get_param('include_all'), FILTER_VALIDATE_BOOLEAN);
        if (!$role) {
            return new WP_Error('invalid_role', __('Role parameter missing', 'artpulse'), ['status' => 400]);
        }

        $available = array_values(DashboardWidgetRegistry::get_for_role($role));
        if (empty($available) && !get_role($role)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Dashboard widgets request for unsupported role: ' . $role);
            }
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        foreach ($available as &$widget) {
            $widget['preview'] = DashboardWidgetRegistry::render($widget['id']);
        }
        unset($widget);

        $core_result   = \ArtPulse\Admin\UserLayoutManager::get_role_layout($role);
        $core_layout   = $core_result['layout'];
        $active_layout = self::convert_to_builder_ids($core_layout);

        $response = [
            'available' => $available,
            'active'    => [
                'role'   => $role,
                'layout' => $active_layout,
            ],
        ];

        if ($include_all) {
            $response['all'] = array_values(DashboardWidgetRegistry::get_all(null, true));
        }

        return rest_ensure_response($response);
    }

    public static function save_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        $role = sanitize_key($data['role'] ?? '');
        if (!$role) {
            return new WP_Error('invalid_role', __('Invalid role', 'artpulse'), ['status' => 400]);
        }
        if (empty(DashboardWidgetRegistry::get_for_role($role)) && !get_role($role)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Attempt to save dashboard layout for unsupported role: ' . $role);
            }
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        if (isset($data['layout']) && is_array($data['layout'])) {
            $layout = self::convert_to_core_ids($data['layout']);
            \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $layout);
        } else {
            $enabled = array_map('sanitize_key', (array) ($data['enabledWidgets'] ?? []));
            $order   = array_map('sanitize_key', (array) ($data['layoutOrder'] ?? []));
            $layout  = self::convert_to_core_ids(array_map(fn($id) => ['id' => $id, 'visible' => true], $order));
            \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $layout);
        }
        return rest_ensure_response(['saved' => true]);
    }

    public static function export_layout(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $role = sanitize_key($request->get_param('role'));
        if (!$role) {
            return new WP_Error('invalid_role', __('Role parameter missing', 'artpulse'), ['status' => 400]);
        }

        $result  = \ArtPulse\Admin\UserLayoutManager::get_role_layout($role);
        $builder = self::convert_to_builder_ids($result['layout']);
        return rest_ensure_response(['role' => $role, 'layout' => $builder]);
    }

    public static function import_layout(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        $role = sanitize_key($data['role'] ?? '');
        if (!$role) {
            return new WP_Error('invalid_role', __('Invalid role', 'artpulse'), ['status' => 400]);
        }
        if (empty(DashboardWidgetRegistry::get_for_role($role)) && !get_role($role)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Attempt to import dashboard layout for unsupported role: ' . $role);
            }
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        $layout = $data['layout'] ?? null;
        if (!is_array($layout)) {
            return new WP_Error('invalid_layout', __('Invalid layout', 'artpulse'), ['status' => 400]);
        }

        $core_layout = self::convert_to_core_ids($layout);
        \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $core_layout);
        return rest_ensure_response(['imported' => true]);
    }
}

