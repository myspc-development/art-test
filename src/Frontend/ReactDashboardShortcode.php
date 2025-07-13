<?php
namespace ArtPulse\Frontend;

use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Helpers\WidgetHelpers;

class ReactDashboardShortcode {
    public static function register(): void {
        add_shortcode('ap_react_dashboard', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
    }

    public static function enqueue_scripts(): void {
        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/app-dashboard.js';
        if (file_exists($path)) {
            wp_enqueue_script(
                'ap-react-dashboard',
                plugins_url('assets/js/app-dashboard.js', ARTPULSE_PLUGIN_FILE),
                ['wp-element'],
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
            if (!empty($widget['callback'])) {
                echo '<div class="ap-widget bg-white p-4 rounded shadow mb-4">';
                echo WidgetHelpers::render_callback_output($widget['callback']);
                echo '</div>';
            }
        }
        echo '</div>';
        return ob_get_clean();
    }
}
