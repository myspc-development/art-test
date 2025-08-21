<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use function ArtPulse\Rest\Util\require_login_and_cap;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;
use ArtPulse\Support\WidgetIds;

class DashboardConfigController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-config')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-config', [
                [
                    'methods'             => 'GET',
                    'callback'            => [self::class, 'get_config'],
                    'permission_callback' => require_login_and_cap(static fn() => current_user_can('read')),
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [self::class, 'save_config'],
                    'permission_callback' => require_login_and_cap(static fn() => current_user_can('manage_options')),
                    'args'                => [
                        'widget_roles' => ['type' => 'object', 'required' => false],
                        'role_widgets' => ['type' => 'object', 'required' => false],
                        'layout'       => ['type' => 'object', 'required' => false],
                        'locked'       => ['type' => 'array', 'required' => false],
                    ],
                ],
            ]);
        }
    }

    public static function get_config(WP_REST_Request $request): WP_REST_Response {
        $visibility   = OptionUtils::get_array_option('artpulse_widget_roles');
        $locked       = get_option('artpulse_locked_widgets', []);
        $role_widgets = OptionUtils::get_array_option('artpulse_dashboard_layouts');
        if (!$role_widgets) {
            $role_widgets = [];
            foreach (DashboardWidgetRegistry::get_role_widget_map() as $role => $widgets) {
                $role_widgets[$role] = array_values(array_map(
                    static fn($w) => sanitize_key($w['id'] ?? ''),
                    $widgets
                ));
            }
        }

        $defs        = DashboardWidgetRegistry::get_all();
        $capabilities = [];
        $excluded     = [];
        foreach ($defs as $id => $def) {
            if (!empty($def['capability'])) {
                $capabilities[$id] = sanitize_key($def['capability']);
            }
            if (!empty($def['exclude_roles'])) {
                $excluded[$id] = array_map('sanitize_key', (array) $def['exclude_roles']);
            }
        }

        return rest_ensure_response([
            'widget_roles' => $visibility,
            'role_widgets' => $role_widgets,
            'locked'       => array_values($locked),
            'capabilities' => $capabilities,
            'excluded_roles' => $excluded,
        ]);
    }

    public static function save_config(WP_REST_Request $request) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Invalid nonce', 'artpulse'), ['status' => 403]);
        }

        $data       = $request->get_json_params();
        $visibility = isset($data['widget_roles']) && is_array($data['widget_roles']) ? $data['widget_roles'] : [];
        foreach ($visibility as $role => &$ids) {
            $ids = array_values(array_unique(array_map([WidgetIds::class, 'canonicalize'], (array) $ids)));
        }
        unset($ids);

        $layout = [];
        if (isset($data['layout']) && is_array($data['layout'])) {
            $layout = $data['layout'];
        } elseif (isset($data['role_widgets']) && is_array($data['role_widgets'])) {
            $layout = $data['role_widgets'];
        }

        foreach ($layout as $role => &$ids) {
            $ids = array_values(array_unique(array_filter(
                array_map([WidgetIds::class, 'canonicalize'], (array) $ids),
                [DashboardWidgetRegistry::class, 'exists']
            )));
        }
        unset($ids);

        $locked = isset($data['locked']) && is_array($data['locked']) ? $data['locked'] : [];
        $locked = array_values(array_unique(array_map([WidgetIds::class, 'canonicalize'], (array) $locked)));

        update_option('artpulse_widget_roles', $visibility);
        update_option('artpulse_dashboard_layouts', $layout);
        update_option('artpulse_locked_widgets', $locked);

        return rest_ensure_response(['saved' => true]);
    }
}

