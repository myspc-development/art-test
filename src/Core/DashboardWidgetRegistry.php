<?php
namespace ArtPulse\Core;

use WP_Roles;

/**
 * Simple registry for dashboard widgets.
 */
class DashboardWidgetRegistry
{
    private static array $widgets = [];

    /**
     * Register a widget.
     */
    public static function register(string $id, callable $callback, string $capability = 'read'): void
    {
        self::$widgets[$id] = [
            'callback'   => $callback,
            'capability' => $capability,
        ];
    }

    /**
     * Get widgets allowed for a user role.
     */
    public static function get_widgets(string $user_role): array
    {
        $role = wp_roles()->get_role($user_role);
        $allowed = [];
        foreach (self::$widgets as $id => $config) {
            $cap = $config['capability'] ?? '';
            if (!$cap || ($role && !empty($role->capabilities[$cap]))) {
                $allowed[$id] = $config['callback'];
            }
        }
        return $allowed;
    }

    /**
     * Fire the registration hook.
     */
    public static function init(): void
    {
        add_action('init', function () {
            do_action('artpulse_register_dashboard_widget');
        });
    }
}
