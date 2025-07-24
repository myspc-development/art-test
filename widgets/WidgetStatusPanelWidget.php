<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Debug panel listing widget registration status.
 */
class WidgetStatusPanelWidget {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void {
        wp_add_dashboard_widget('ap_widget_status_panel', __('Widget Status Panel', 'artpulse'), [self::class, 'render']);
    }

    public static function render(): void {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        global $ap_widget_status;
        if (!$ap_widget_status) {
            echo esc_html__('No widget data available.', 'artpulse');
            return;
        }
        echo '<h4>' . esc_html__('Registered Widgets', 'artpulse') . '</h4>';
        echo '<ul>'; 
        foreach ($ap_widget_status['registered'] as $file) {
            echo '<li>' . esc_html($file) . '</li>';
        }
        echo '</ul>';
        if ($ap_widget_status['missing']) {
            echo '<h4>' . esc_html__('Missing Files', 'artpulse') . '</h4><ul>';
            foreach ($ap_widget_status['missing'] as $file) {
                echo '<li>' . esc_html($file) . '</li>';
            }
            echo '</ul>';
        }
        if ($ap_widget_status['unregistered']) {
            echo '<h4>' . esc_html__('Unregistered Files', 'artpulse') . '</h4><ul>';
            foreach ($ap_widget_status['unregistered'] as $file) {
                echo '<li>' . esc_html($file) . '</li>';
            }
            echo '</ul>';
        }
    }
}

WidgetStatusPanelWidget::register();
