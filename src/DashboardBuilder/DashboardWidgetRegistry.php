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
     *     'roles' => ['artist', 'org_manager'],
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
        ];
        $args = array_merge($defaults, $args);
        if (!is_callable($args['render_callback'])) {
            return;
        }
        self::$widgets[$id] = [
            'id' => $id,
            'title' => (string) $args['title'],
            'render_callback' => $args['render_callback'],
            'roles' => array_map('sanitize_key', (array) $args['roles']),
            'file' => (string) $args['file'],
        ];
    }

    /**
     * Get all registered widgets.
     */
    public static function get_all(): array {
        return self::$widgets;
    }

    /**
     * Get widgets available for a specific role.
     */
    public static function get_for_role(string $role): array {
        $role = sanitize_key($role);
        return array_filter(
            self::$widgets,
            static function($w) use ($role) {
                return empty($w['roles']) || in_array($role, (array) $w['roles'], true);
            }
        );
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
