<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Simple dashboard widget showing basic organization analytics.
 */
class OrgAnalyticsWidget {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void {
        wp_add_dashboard_widget(
            'artpulse_analytics_widget',
            __('Organization Analytics', 'artpulse'),
            [self::class, 'render']
        );
    }

    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
        if (!current_user_can('view_analytics')) {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo '<p>' . esc_html__('Basic traffic and engagement metrics will appear here.', 'artpulse') . '</p>';
    }
}

OrgAnalyticsWidget::register();

