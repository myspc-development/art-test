<?php
namespace ArtPulse\Core;

use WP_Roles;

/**
 * Simple registry for dashboard widgets.
 */
class DashboardWidgetRegistry
{
    /**
     * @var array<string,array{
     *     label:string,
     *     icon:string,
     *     description:string,
     *     callback:callable,
     *     capability:string,
     *     settings:array,
     *     category?:string,
     *     roles?:array
     * }>
     */
    private static array $widgets = [];

    /**
     * Register a widget and its settings.
     *
     * @param callable $callback Callback used to render the widget. Must be
     *                           callable.
     */
    public static function register(
        string $id,
        string $label,
        string $icon,
        string $description,
        callable $callback,
        $capability_or_options = 'read',
        array $settings_schema = []
    ): void {
        // Callback must be valid to render the widget.
        if (!is_callable($callback)) {
            trigger_error('Dashboard widget callback not callable: ' . $id, E_USER_WARNING);
            return;
        }
        $capability = 'read';
        $extra       = [];

        if (is_string($capability_or_options)) {
            $capability = $capability_or_options;
        } elseif (is_array($capability_or_options)) {
            $extra = $capability_or_options;
        }

        $entry = [
            'label'       => $label,
            'icon'        => $icon,
            'description' => $description,
            'callback'    => $callback,
            'capability'  => $capability,
            'settings'    => $settings_schema,
        ];

        if (!empty($extra['category'])) {
            $entry['category'] = $extra['category'];
        }
        if (!empty($extra['roles'])) {
            $entry['roles'] = (array) $extra['roles'];
        }

        self::$widgets[$id] = $entry;
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
     *
     * @param bool $include_schema Include the settings schema for each widget.
     */
    public static function get_definitions(bool $include_schema = false): array
    {
        $defs = [];
        foreach (self::$widgets as $id => $config) {
            $def = [
                'id'          => $id,
                'name'        => $config['label'],
                'icon'        => $config['icon'],
                'description' => $config['description'],
            ];
            if (isset($config['category'])) {
                $def['category'] = $config['category'];
            }
            if (isset($config['roles'])) {
                $def['roles'] = $config['roles'];
            }
            if ($include_schema) {
                $def['settings'] = $config['settings'];
            }
            $defs[] = $def;
        }

        return apply_filters('ap_dashboard_widget_definitions', $defs);
    }

    /**
     * Get a single widget callback by ID.
     */
    public static function get_widget_callback(string $id): ?callable
    {
        return self::$widgets[$id]['callback'] ?? null;
    }

    /**
     * Get the settings schema for a widget.
     */
    public static function get_widget_schema(string $id): array
    {
        return self::$widgets[$id]['settings'] ?? [];
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
