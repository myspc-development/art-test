<?php
namespace ArtPulse\Core;

/**
 * Loads widget definitions from a config file and registers them with the
 * DashboardWidgetRegistry.
 */
class WidgetRegistryLoader
{
    /**
     * Cached configuration array.
     *
     * @var array<string, array>
     */
    private static array $config = [];

    /**
     * Flag to ensure widgets are only registered once.
     */
    private static bool $registered = false;

    /**
     * Retrieve the widget configuration.
     *
     * @return array<string, array>
     */
    public static function get_config(): array
    {
        if (self::$config) {
            return self::$config;
        }

        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'config/dashboard-widgets.php';
        $config = file_exists($path) ? include $path : [];
        if (!is_array($config)) {
            $config = [];
        }

        /**
         * Allow third-parties to modify widget metadata before registration.
         */
        $config = apply_filters('ap_dashboard_widgets_metadata', $config);

        self::$config = $config;
        return self::$config;
    }

    /**
     * Register widgets defined in the configuration file.
     */
    public static function register_widgets(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        foreach (self::get_config() as $id => $def) {
            $id = sanitize_key($id);
            if (!$id) {
                continue;
            }

            $label = $def['label'] ?? '';
            $roles = $def['roles'] ?? [];

            // Determine the render callback.
            $callback = null;
            if (isset($def['class']) && is_string($def['class']) && method_exists($def['class'], 'render')) {
                $callback = [$def['class'], 'render'];
            } elseif (isset($def['callback'])) {
                $callback = $def['callback'];
            }

            if (!$label || !is_array($roles) || !$callback) {
                // Skip invalid widget definitions.
                continue;
            }

            if (DashboardWidgetRegistry::exists($id)) {
                // Skip if already registered elsewhere.
                continue;
            }

            $icon        = $def['icon'] ?? '';
            $description = $def['description'] ?? '';

            // Pass through additional optional configuration fields.
            $options = $def;
            unset($options['class'], $options['label'], $options['description'], $options['icon'], $options['callback']);
            $options['roles'] = $roles;

            DashboardWidgetRegistry::register($id, $label, $icon, $description, $callback, $options);
        }
    }
}
