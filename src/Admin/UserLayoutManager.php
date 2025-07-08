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
        $ordered = [];
        foreach ($layout as $item) {
            if (is_array($item) && isset($item['id'])) {
                $id  = sanitize_key($item['id']);
                $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
            } else {
                $id  = sanitize_key($item);
                $vis = true;
            }
            if (in_array($id, $valid, true)) {
                $ordered[] = ['id' => $id, 'visible' => $vis];
            }
        }

        update_user_meta($user_id, self::META_KEY, $ordered);
    }

    /**
     * Get the default layout for a role.
     */
    public static function get_role_layout(string $role): array
    {
        $config = get_option('ap_dashboard_widget_config', []);
        $layout = $config[$role] ?? [];
        if (is_array($layout) && !empty($layout)) {
            $valid   = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
            $ordered = [];
            foreach ($layout as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $id  = sanitize_key($item['id']);
                    $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
                } else {
                    $id  = sanitize_key($item);
                    $vis = true;
                }
                if (in_array($id, $valid, true)) {
                    $ordered[] = ['id' => $id, 'visible' => $vis];
                }
            }
            if ($ordered) {
                return $ordered;
            }
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_map(
            fn($def) => ['id' => $def['id'], 'visible' => true],
            $defs
        );
    }

    /**
     * Save the default layout for a role.
     */
    public static function save_role_layout(string $role, array $layout): void
    {
        $valid  = array_column(DashboardWidgetRegistry::get_definitions(), 'id');
        $ordered = [];
        foreach ($layout as $item) {
            if (is_array($item) && isset($item['id'])) {
                $id  = sanitize_key($item['id']);
                $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
            } else {
                $id  = sanitize_key($item);
                $vis = true;
            }
            if (in_array($id, $valid, true)) {
                $ordered[] = ['id' => $id, 'visible' => $vis];
            }
        }

        $config = get_option('ap_dashboard_widget_config', []);
        $config[sanitize_key($role)] = $ordered;
        update_option('ap_dashboard_widget_config', $config);
    }

    public static function export_layout(string $role): string
    {
        return json_encode(self::get_role_layout($role), JSON_PRETTY_PRINT);
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

        $role = self::get_primary_role($user_id);
        $layout = self::get_role_layout($role);
        if (!empty($layout)) {
            return $layout;
        }

        $defs = DashboardWidgetRegistry::get_definitions();
        return array_map(
            fn($def) => ['id' => $def['id'], 'visible' => true],
            $defs
        );
    }

    /**
     * Get a user's primary role.
     */
    private static function get_primary_role(int $user_id): string
    {
        $user = get_userdata($user_id);
        return $user && !empty($user->roles) ? sanitize_key($user->roles[0]) : 'subscriber';
    }

}
