<?php
namespace ArtPulse\Core;

class DashboardRenderer
{
    /**
     * Determine if a widget should be cached.
     */
    public static function shouldCache(string $widget_id, int $user_id, array $widget): bool
    {
        if (!$user_id) {
            return false; // never cache for logged-out users
        }

        $flag = (bool) ($widget['cache'] ?? false);

        /**
         * Allow plugins to override widget caching.
         */
        return (bool) apply_filters('ap_dashboard_widget_should_cache', $flag, $widget_id, $user_id, $widget);
    }

    public static function render(string $widget_id, ?int $user_id = null): string
    {
        $user_id = $user_id ?? get_current_user_id();
        $role    = DashboardController::get_role($user_id);
        $widget  = DashboardWidgetRegistry::get_widget($widget_id);

        if (!$widget) {
            error_log("\xF0\x9F\x9A\xAB Widget '{$widget_id}' not found in registry.");
            return '';
        }

        $allowed_roles = [];
        $class         = $widget['class'] ?? '';
        if ($class && class_exists($class) && is_callable([$class, 'roles'])) {
            $allowed_roles = (array) $class::roles();
        } elseif (!empty($widget['roles'])) {
            $allowed_roles = (array) $widget['roles'];
        }

        if ($allowed_roles && !in_array($role, $allowed_roles, true)) {
            error_log("\xF0\x9F\x9A\xAB Widget '{$widget_id}' not allowed for role '{$role}'.");
            return '';
        }

        $cache_key = "ap_widget_{$widget_id}_{$user_id}";
        $output    = '';

        if (self::shouldCache($widget_id, $user_id, $widget)) {
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                return (string) $cached;
            }
        }

        $start = microtime(true);

        if ($class && class_exists($class) && is_callable([$class, 'render'])) {
            ob_start();
            $result = $class::render();
            $buffer = ob_get_clean();
            $output = $buffer . (is_string($result) ? $result : '');
        } elseif (has_action("ap_render_dashboard_widget_{$widget_id}")) {
            ob_start();
            do_action("ap_render_dashboard_widget_{$widget_id}", $user_id);
            $output = ob_get_clean();
        } elseif (isset($widget['callback']) && is_callable($widget['callback'])) {
            ob_start();
            $result = call_user_func($widget['callback']);
            $buffer = ob_get_clean();
            $output = $buffer . (is_string($result) ? $result : '');
        } else {
            error_log("\xF0\x9F\x9A\xAB Invalid callback for widget '{$widget_id}'.");
        }

        $output = apply_filters('ap_render_dashboard_widget_output', $output, $widget_id, $user_id, $widget);

        $elapsed = microtime(true) - $start;
        error_log(sprintf('⏱️ Widget %s rendered in %.4fs', $widget_id, $elapsed));

        if (self::shouldCache($widget_id, $user_id, $widget)) {
            $ttl = (int) apply_filters('ap_dashboard_widget_cache_ttl', MINUTE_IN_SECONDS * 10, $widget_id, $user_id, $widget);
            if ($ttl > 0) {
                set_transient($cache_key, $output, $ttl);
            }
        }

        return $output;
    }
}
