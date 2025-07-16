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
     *     category?:string,
     *     roles?:array,
     *     settings?:array
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
        array $options = [] // supports 'category', 'roles' and optional 'settings'
    ): void {
        // Prevent duplicate IDs or labels.
        if (isset(self::$widgets[$id])) {
            trigger_error('Dashboard widget ID already registered: ' . $id, E_USER_WARNING);
            return;
        }
        foreach (self::$widgets as $w) {
            if ($w['label'] === $label) {
                trigger_error('Dashboard widget label already registered: ' . $label, E_USER_WARNING);
                return;
            }
        }

        // Callback must be valid to render the widget.
        if (!is_callable($callback)) {
            $callback = [self::class, 'render_widget_fallback'];
        }

        self::$widgets[$id] = [
            'label'       => $label,
            'icon'        => $icon,
            'description' => $description,
            'callback'    => $callback,
            'category'    => $options['category'] ?? '',
            'roles'       => $options['roles'] ?? [],
            'settings'    => $options['settings'] ?? [],
        ];
    }

    /**
     * Simplified widget registration used by generic dashboards.
     */
    public static function register_widget(string $id, array $args): void
    {
        $id = sanitize_key($id);
        if (!$id) {
            return;
        }

        if (isset(self::$widgets[$id])) {
            trigger_error('Dashboard widget ID already registered: ' . $id, E_USER_WARNING);
            return;
        }

        $label = $args['label'] ?? 'Untitled';
        foreach (self::$widgets as $w) {
            if (($w['label'] ?? '') === $label) {
                trigger_error('Dashboard widget label already registered: ' . $label, E_USER_WARNING);
                return;
            }
        }
        $args['label'] = $label;

        if (empty($args['callback']) && isset($args['template'])) {
            $template = $args['template'];
            $args['callback'] = static function () use ($template) {
                $path = locate_template($template);
                if (!$path) {
                    $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . $template;
                }

                if (file_exists($path)) {
                    include $path;
                }
            };
        }

        if (empty($args['callback']) || !is_callable($args['callback'])) {
            $args['callback'] = [self::class, 'render_widget_fallback'];
        }

        $args['id'] = $id;

        self::$widgets[$id] = $args;
    }

    public static function render_widget_fallback(): void
    {
        echo '<p><strong>Widget callback is missing or invalid.</strong></p>';
    }

    private static function include_template(string $template): void
    {
        $path = locate_template($template);
        if (!$path) {
            $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'templates/' . $template;
        }

        if (file_exists($path)) {
            include $path;
        } else {
            echo '<p>' . esc_html__('No content available.', 'artpulse') . '</p>';
        }
    }

    public static function render_widget_news(): void
    {
        self::include_template('widgets/widget-news.php');
    }

    public static function render_widget_events(): void
    {
        self::include_template('widgets/events.php');
    }

    public static function render_widget_favorites(): void
    {
        self::include_template('widgets/favorites.php');
    }

    public static function render_widget_for_you(): void
    {
        self::include_template('widgets/widget-for-you.php');
    }

    /**
     * Retrieve a widget configuration by ID.
     */
    public static function get_widget(string $id): ?array
    {
        return self::$widgets[$id] ?? null;
    }

    /**
     * Return all registered widgets.
     *
     * @return array<string,array>
     */
    public static function get_all(): array
    {
        return self::$widgets;
    }

    /**
     * Get a single widget configuration by ID.
     */
    public static function get(string $id): ?array
    {
        return self::$widgets[$id] ?? null;
    }

    /**
     * Get widget callbacks allowed for a user role.
     */
    public static function get_widgets(string $user_role): array
    {
        $allowed = [];
        foreach (self::$widgets as $id => $config) {
            if (!empty($config['roles']) && !in_array($user_role, (array) $config['roles'], true)) {
                continue;
            }
            $allowed[$id] = $config['callback'];
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
            // Sanitize widget configuration to avoid undefined index warnings.
            $label       = isset($config['label']) ? $config['label'] : 'Unnamed Widget';
            $icon        = isset($config['icon']) ? $config['icon'] : 'dashicons-admin-generic';
            $description = isset($config['description']) ? $config['description'] : '';

            $def = [
                'id'          => $id,
                'name'        => $label,
                'icon'        => $icon,
                'description' => $description,
            ];
            if (isset($config['category'])) {
                $def['category'] = $config['category'];
            }
            if (isset($config['roles'])) {
                $def['roles'] = $config['roles'];
            }
            if ($include_schema) {
                $def['settings'] = $config['settings'] ?? [];
            }
            $defs[$id] = $def;
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
     * Get widgets definitions filtered by role.
     */
    public static function get_widgets_by_role(string $role): array
    {
        $defs = [];
        foreach (self::$widgets as $id => $cfg) {
            if (!empty($cfg['roles']) && !in_array($role, (array) $cfg['roles'], true)) {
                continue;
            }
            $defs[$id] = $cfg;
        }
        return $defs;
    }

    /**
     * Get a random subset of widgets for a role.
     */
    public static function get_random(string $role, int $limit = 1): array
    {
        $widgets = self::get_widgets_by_role($role);
        if (!$widgets) {
            return [];
        }
        $keys = array_keys($widgets);
        shuffle($keys);
        $keys = array_slice($keys, 0, $limit);
        return array_intersect_key($widgets, array_flip($keys));
    }

    /**
     * Get widgets that belong to a specific category.
     */
    public static function get_by_category(string $category): array
    {
        return array_filter(
            self::$widgets,
            static fn($cfg) => isset($cfg['category']) && $cfg['category'] === $category
        );
    }

    /**
     * Register default widgets and fire registration hook.
     */
    public static function init(): void
    {
        $register = [self::class, 'register_widget'];

        $register('widget_news', [
            'id'          => 'widget_news',
            'label'       => __('News', 'artpulse'),
            'icon'        => 'dashicons-megaphone',
            'description' => __('Latest updates from ArtPulse.', 'artpulse'),
            'callback'    => [self::class, 'render_widget_news'],
            'roles'       => ['member', 'artist'],
        ]);

        $register('widget_events', [
            'id'          => 'widget_events',
            'label'       => __('Upcoming Events', 'artpulse'),
            'icon'        => 'dashicons-calendar-alt',
            'description' => __('Events happening soon.', 'artpulse'),
            'callback'    => [self::class, 'render_widget_events'],
            'roles'       => ['member', 'organization'],
        ]);

        $register('widget_favorites', [
            'id'          => 'widget_favorites',
            'label'       => __('Favorites Overview', 'artpulse'),
            'icon'        => 'dashicons-star-filled',
            'description' => __('Artists you have saved.', 'artpulse'),
            'callback'    => [self::class, 'render_widget_favorites'],
            'roles'       => ['member'],
        ]);

        $register('widget_for_you', [
            'id'          => 'widget_for_you',
            'label'       => __('For You', 'artpulse'),
            'icon'        => 'dashicons-thumbs-up',
            'description' => __('Recommended content.', 'artpulse'),
            'callback'    => [self::class, 'render_widget_for_you'],
            'roles'       => ['member', 'artist'],
        ]);

        do_action('artpulse_register_dashboard_widget');
    }
}
