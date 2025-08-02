<?php
namespace ArtPulse\Widgets;

if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Debug panel listing widget registration status.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetStatusPanelWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'widget_status_panel',
            __('Widget Status Panel', 'artpulse'),
            'info',
            __('Debug information about widget registration.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['administrator'] ]
        );
    }

    public static function render(): void {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        if (!current_user_can('manage_options')) {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
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
