<?php
namespace ArtPulse\Frontend;

use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Helpers\WidgetHelpers;

class ReactDashboardShortcode {
    public static function register(): void {
        \ArtPulse\Core\ShortcodeRegistry::register('ap_react_dashboard', 'React Dashboard', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
    }

    public static function enqueue_scripts(): void {
        $grid_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'js/react-grid-layout.min.js';
        if (file_exists($grid_path)) {
            wp_enqueue_script(
                'react-grid-layout',
                plugins_url('js/react-grid-layout.min.js', ARTPULSE_PLUGIN_FILE),
                ['react', 'react-dom'],
                filemtime($grid_path),
                true
            );
            // Provide backward-compatible global for older builds.
            wp_add_inline_script('react-grid-layout', 'window.GridLayout = window.ReactGridLayout;', 'after');
        }

        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'dist/app-dashboard.js';
        if (file_exists($path)) {
            wp_enqueue_script(
                'ap-react-dashboard',
                plugins_url('dist/app-dashboard.js', ARTPULSE_PLUGIN_FILE),
                ['wp-element', 'react-grid-layout'],
                filemtime($path),
                true
            );
        }
    }

    public static function render(): string {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your dashboard.', 'artpulse') . '</p>';
        }

        $widgets = DashboardWidgetTools::get_role_widgets_for_current_user();
        ob_start();
        echo '<div id="ap-dashboard-root">';
        foreach ($widgets as $widget) {
            $id    = isset($widget['id']) ? esc_attr($widget['id']) : '';
            $attrs = [
                'class'         => 'ap-widget bg-white p-4 rounded shadow mb-4',
                'data-widget-id'=> $id,
            ];
            if (!empty($widget['rest'])) {
                $attrs['data-rest'] = esc_attr($widget['rest']);
            }
            echo '<div';
            foreach ($attrs as $key => $val) {
                echo ' ' . $key . '="' . $val . '"';
            }
            echo '>';
            if (!empty($widget['callback']) && is_callable($widget['callback'])) {
                echo WidgetHelpers::render_callback_output($widget['callback']);
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }
}
