<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Manage dashboard widget layouts for users and roles.
 */
class UserLayoutManager
{
    public const META_KEY = 'ap_dashboard_layout';
    public const ROLE_KEY_PREFIX = 'ap_dashboard_layout_role_';
    /**
     * Get a user's widget layout with fallbacks.
     */
    public static function get_layout(int $user_id): array
    {
        $layout = get_user_meta($user_id, self::META_KEY, true);
        if (is_array($layout) && !empty($layout)) {
            return array_map('sanitize_key', $layout);
        }

        $user = get_userdata($user_id);
        if ($user && !empty($user->roles)) {
            foreach ($user->roles as $role) {
                $role_layout = self::get_role_layout($role);
                if ($role_layout) {
                    return $role_layout;
                }
            }
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_column($defs, 'id');
    }

    /**
     * Save a user's widget layout.
     */
    public static function save_layout(int $user_id, array $layout): void
    {
        $layout = array_map('sanitize_key', $layout);
        $valid  = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $layout = array_values(array_intersect(array_unique($layout), $valid));
        update_user_meta($user_id, self::META_KEY, $layout);
    }

    /**
     * Get the default layout for a role.
     */
    public static function get_role_layout(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        $layout = $config[$role] ?? [];
        if (is_array($layout) && !empty($layout)) {
            return array_map('sanitize_key', $layout);
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_column($defs, 'id');
    }

    /**
     * Save the default layout for a role.
     */
    public static function save_role_layout(string $role, array $layout): void
    {
        $layout = array_map('sanitize_key', $layout);
        $valid  = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $layout = array_values(array_intersect(array_unique($layout), $valid));
        $config = get_option('ap_dashboard_widget_config', []);
        $config[sanitize_key($role)] = $layout;
        update_option('ap_dashboard_widget_config', $config);
    }

    public static function export_layout(string $role): string
    {
        return json_encode(self::get_role_layout($role), JSON_PRETTY_PRINT);
    }

    public static function import_layout(string $role, string $json): bool
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return false;
        }

        $valid   = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $layout  = [];
        foreach ($data as $w) {
            $key = sanitize_key($w);
            if (in_array($key, $valid, true)) {
                $layout[] = $key;
            }
        }

        self::save_role_layout($role, $layout);
        return true;
    }
}
