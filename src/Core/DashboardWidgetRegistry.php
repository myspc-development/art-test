<?php
namespace ArtPulse\Core;

use WP_Roles;

/**
 * Simple registry for dashboard widgets.
 */
class DashboardWidgetRegistry
{
    /**
     * @var array<string,array{label:string,icon:string,description:string,callback:callable,capability:string}>
     */
    private static array $widgets = [];

    /**
     * Register a widget and its settings.
     */
    public static function register(
        string $id,
        string $label,
        string $icon,
        string $description,
        callable $callback,
        string $capability = 'read'
    ): void {
        self::$widgets[$id] = [
            'label'       => $label,
            'icon'        => $icon,
            'description' => $description,
            'callback'    => $callback,
            'capability'  => $capability,
        ];
    }

    /**
     * Get widget callbacks allowed for a user role.
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
     * Return full widget definitions.
     */
    public static function get_definitions(): array
    {
        $defs = [];
        foreach (self::$widgets as $id => $config) {
            $defs[] = [
                'id'          => $id,
                'name'        => $config['label'],
                'icon'        => $config['icon'],
                'description' => $config['description'],
            ];
        }

        return apply_filters('ap_dashboard_widget_definitions', $defs);
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
