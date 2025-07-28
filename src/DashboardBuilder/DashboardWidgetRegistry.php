<?php
namespace ArtPulse\DashboardBuilder;

/**
 * Simple widget registry for the Dashboard Builder.
 */
class DashboardWidgetRegistry {
    /** @var array<string,array> */
    private static array $widgets = [];

    /**
     * Register a widget definition.
     *
     * Example:
     * DashboardWidgetRegistry::register('event_summary', [
     *     'title' => 'Upcoming Events',
     *     'render_callback' => 'render_event_summary',
     *     'roles' => ['artist', 'organization'],
     *     'visibility' => 'public'
     * ]);
     */
    public static function register(string $id, array $args): void {
        $id = sanitize_key($id);
        if (!$id) {
            return;
        }
        $defaults = [
            'title' => '',
            'render_callback' => null,
            'roles' => [],
            'file' => '',
            'visibility' => 'public',
        ];
        $args = array_merge($defaults, $args);
        if (!is_callable($args['render_callback'])) {
            $args['render_callback'] = static function () {};
        }
        $visibility = in_array($args['visibility'], ['public', 'internal', 'deprecated'], true)
            ? $args['visibility']
            : 'public';
        self::$widgets[$id] = [
            'id' => $id,
            'title' => (string) $args['title'],
            'render_callback' => $args['render_callback'],
            'roles' => array_map('sanitize_key', (array) $args['roles']),
            'file' => (string) $args['file'],
            'visibility' => $visibility,
        ];
    }

    /**
     * Get all registered widgets.
     *
     * @param string|null $visibility Optional visibility filter.
     */
    public static function get_all(?string $visibility = null): array {
        if ($visibility !== null) {
            return array_filter(
                self::$widgets,
                static fn($w) => ($w['visibility'] ?? 'public') === $visibility
            );
        }

        return self::$widgets;
    }

    /**
     * Backwards compatibility alias for legacy code.
     *
     * @deprecated Use get_all() instead.
     */
    public static function get_all_widgets(?string $visibility = null): array {
        return self::get_all($visibility);
    }

    /**
     * Get widgets available for a specific role.
     */
    public static function get_for_role(string $role): array {
        $role = sanitize_key($role);
        return array_filter(
            self::$widgets,
            static function ($w) use ($role) {
                $roles = isset($w['roles']) ? (array) $w['roles'] : [];
                return in_array($role, $roles, true);
            }
        );
    }

    /**
     * Retrieve a widget configuration by ID.
     */
    public static function get_widget(string $id): ?array {
        $widgets = self::get_all();
        return $widgets[$id] ?? null;
    }

    /**
     * Render a widget by ID and return the output.
     */
    public static function render(string $id): string {
        if (!isset(self::$widgets[$id])) {
            return '';
        }

        static $stack = [];
        if (isset($stack[$id])) {
            return '';
        }

        $stack[$id] = true;
        ob_start();
        try {
            call_user_func(self::$widgets[$id]['render_callback']);
        } catch (\Throwable $e) {
            $file = self::$widgets[$id]['file'] ?? 'unknown';
            error_log('[DashboardBuilder] Failed rendering widget ' . $id . ' (' . $file . '): ' . $e->getMessage());
        }
        $html = ob_get_clean();
        unset($stack[$id]);

        return $html;
    }
}
