<?php
declare(strict_types=1);

/** Recursively replace closures/invokables with serializable strings. */
if (!function_exists('ap_strip_closures')) {
    function ap_strip_closures($value) {
        if ($value instanceof \Closure) {
            return '__closure__';
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = ap_strip_closures($v);
            }
            return $value;
        }
        // Replace invokable objects with a descriptive string
        if (is_object($value) && is_callable($value)) {
            return get_class($value) . '::__invoke';
        }
        return $value;
    }
}

/**
 * Test-only: sanitize final dashboard widget definitions before the plugin
 * stores/uses them. Runs at very high priority to catch anything other code added.
 */
if (!function_exists('ap_register_dashboard_definition_sanitizer')) {
    function ap_register_dashboard_definition_sanitizer(): void {
        $filter = static function ($defs) {
            if (!is_array($defs)) {
                return $defs;
            }
            foreach ($defs as $id => $def) {
                $defs[$id] = ap_strip_closures($def);
            }
            return $defs;
        };
        // Cover both possible filter names your code may use.
        add_filter('ap_dashboard_widget_definitions', $filter, 999, 1);
        add_filter('artpulse_dashboard_widget_definitions', $filter, 999, 1);
    }
}
