<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\DashboardWidgetRegistry;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class WidgetSettingsRestController
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
        register_rest_route('artpulse/v1', '/widget-settings/(?P<id>[a-z0-9_-]+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_settings'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/widget-settings/(?P<id>[a-z0-9_-]+)', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'save_settings'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function get_settings(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id     = sanitize_key($request['id']);
        $global = (bool) $request->get_param('global');
        $schema = DashboardWidgetRegistry::get_widget_schema($id);

        if (empty($schema)) {
            return new WP_Error('invalid_widget', __('Unknown widget.', 'artpulse'), ['status' => 404]);
        }

        $settings = $global
            ? (array) get_option('ap_widget_settings_' . $id, [])
            : (array) get_user_meta(get_current_user_id(), 'ap_widget_settings_' . $id, true);

        $result = [];
        foreach ($schema as $field) {
            if (!isset($field['key'])) {
                continue;
            }
            $key = $field['key'];
            $result[$key] = $settings[$key] ?? ($field['default'] ?? '');
        }

        return rest_ensure_response([
            'schema'   => $schema,
            'settings' => $result,
        ]);
    }

    public static function save_settings(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $id     = sanitize_key($request['id']);
        $global = (bool) $request->get_param('global');
        $schema = DashboardWidgetRegistry::get_widget_schema($id);

        if (empty($schema)) {
            return new WP_Error('invalid_widget', __('Unknown widget.', 'artpulse'), ['status' => 404]);
        }

        $raw = (array) $request->get_param('settings');
        $sanitized = [];

        foreach ($schema as $field) {
            if (!isset($field['key'])) {
                continue;
            }
            $key  = $field['key'];
            $type = $field['type'] ?? 'text';
            $val  = $raw[$key] ?? null;
            if ($type === 'checkbox') {
                $sanitized[$key] = !empty($val);
            } elseif ($type === 'number') {
                $sanitized[$key] = $val !== null ? floatval($val) : ($field['default'] ?? 0);
            } else {
                $sanitized[$key] = $val !== null ? sanitize_text_field($val) : ($field['default'] ?? '');
            }
        }

        if ($global) {
            if (!current_user_can('manage_options')) {
                return new WP_Error('forbidden', __('Permission denied', 'artpulse'), ['status' => 403]);
            }
            update_option('ap_widget_settings_' . $id, $sanitized);
        } else {
            update_user_meta(get_current_user_id(), 'ap_widget_settings_' . $id, $sanitized);
        }

        return rest_ensure_response(['saved' => true]);
    }
}
