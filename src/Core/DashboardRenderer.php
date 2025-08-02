<?php
namespace ArtPulse\Core;

class DashboardRenderer
{
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

        $output = '';

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

        return $output;
    }
}
