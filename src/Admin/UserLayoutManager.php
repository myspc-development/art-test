<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

/**
 * Manage dashboard widget layouts for users and roles.
 */
class UserLayoutManager
{
    public const META_KEY = 'ap_dashboard_layout';
    public const VIS_META_KEY = 'ap_widget_visibility';
    /**
     * Get a user's widget layout with fallbacks.
     *
     * @deprecated Use get_layout_for_user() instead.
     */
    public static function get_layout(int $user_id): array
    {
        return self::get_layout_for_user($user_id);
    }

    /**
     * Save a user's widget layout.
     */
    public static function save_layout(int $user_id, array $layout): void
    {
        $valid   = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $ordered = \ArtPulse\Core\LayoutUtils::normalize_layout($layout, $valid);

        update_user_meta($user_id, self::META_KEY, $ordered);
    }

    /**
     * Alias for save_layout for backward compatibility.
     */
    public static function save_user_layout(int $user_id, array $layout): void
    {
        self::save_layout($user_id, $layout);
    }

    /**
     * Get the default layout for a role.
     *
     * @return array{layout:array<array<string,mixed>>,logs:array<int,string>}
     */
    public static function get_role_layout(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        $entry  = $config[$role] ?? [];
        $layout = [];

        if (is_array($entry) && isset($entry['layout'])) {
            $layout = $entry['layout'];
        } elseif (is_array($entry)) {
            $layout = $entry;
        }

        if (is_array($layout) && !empty($layout)) {

            if ($ordered) {
                return [
                    'layout' => $ordered,
                    'logs'   => $logs,
                ];
            }
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        $defs = array_filter(
            $defs,
            fn($def) => $def['id'] !== 'artpulse_dashboard_widget'
        );

        return [
            'layout' => array_map(
                fn($def) => ['id' => $def['id'], 'visible' => true],
                $defs
            ),
            'logs'   => [],
        ];
    }

    /**
     * Save the default layout for a role.
     */
    public static function save_role_layout(string $role, array $layout): void
    {
        $valid  = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $ordered = \ArtPulse\Core\LayoutUtils::normalize_layout($layout, $valid);

        $config = get_option('ap_dashboard_widget_config', []);
        $role_key = sanitize_key($role);
        $entry = $config[$role_key] ?? [];
        $style = [];
        if (is_array($entry) && isset($entry['style'])) {
            $style = $entry['style'];
        }

        $config[$role_key] = [ 'layout' => $ordered ];
        if ($style) {
            $config[$role_key]['style'] = $style;
        }

        update_option('ap_dashboard_widget_config', $config);
    }

    public static function export_layout(string $role): string
    {
        return json_encode(self::get_role_layout($role)['layout'], JSON_PRETTY_PRINT);
    }

    public static function import_layout(string $role, string $json): bool
    {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            self::save_role_layout($role, $decoded);
            return true;
        }
        return false;
    }

    /**
     * Get style configuration for a role.
     */
    public static function get_role_style(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        $entry = $config[$role] ?? [];
        if (is_array($entry) && isset($entry['style']) && is_array($entry['style'])) {
            return $entry['style'];
        }
        return [];
    }

    /**
     * Save style configuration for a role.
     */
    public static function save_role_style(string $role, array $style): void
    {
        $sanitized = [];
        foreach ($style as $k => $v) {
            $key = sanitize_key($k);
            $val = is_string($v) ? sanitize_text_field($v) : $v;
            $sanitized[$key] = $val;
        }

        $config = get_option('ap_dashboard_widget_config', []);
        $role_key = sanitize_key($role);
        $entry = $config[$role_key] ?? [];

        if (!is_array($entry)) {
            $entry = [ 'layout' => is_array($entry) ? $entry : [] ];
        }

        $entry['style'] = $sanitized;
        $config[$role_key] = $entry;

        update_option('ap_dashboard_widget_config', $config);
    }

    public static function reset_layout_for_role(string $role): void
    {
        $config = get_option('ap_dashboard_widget_config', []);
        $role_key = sanitize_key($role);
        if (isset($config[$role_key])) {
            unset($config[$role_key]);
            update_option('ap_dashboard_widget_config', $config);
        }
    }

    /**
     * Remove a user's saved dashboard layout and visibility.
     */
    public static function reset_user_layout(int $user_id): void
    {
        delete_user_meta($user_id, self::META_KEY);
        delete_user_meta($user_id, self::VIS_META_KEY);
    }

    /**
     * Retrieve the raw dashboard layout for a user.
     */
    public static function get_user_layout(int $user_id): array
    {
        $layout = get_user_meta($user_id, self::META_KEY, true);
        return is_array($layout) ? $layout : [];
    }

    /**
     * Determine a user's dashboard layout with fallbacks.
     */
    public static function get_layout_for_user(int $user_id): array
    {
        $layout = self::get_user_layout($user_id);
        if (!empty($layout)) {
            return $layout;
        }

        $role   = self::get_primary_role($user_id);
        $result = self::get_role_layout($role);
        $layout = $result['layout'];
        if (!empty($layout)) {
            return $layout;
        }

        $all = DashboardWidgetRegistry::get_all();
        return array_map(
            fn($id) => ['id' => $id, 'visible' => true],
            array_keys($all)
        );
    }

    /**
     * Get a user's primary role.
     */
    public static function get_primary_role(int $user_id): string
    {
        $user = get_userdata($user_id);
        return $user && !empty($user->roles) ? $user->roles[0] : 'subscriber';
    }

}
