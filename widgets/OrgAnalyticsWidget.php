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
        if (!current_user_can('view_analytics')) {
            return;
        }
        wp_add_dashboard_widget(
            'artpulse_analytics_widget',
            __('Organization Analytics', 'artpulse'),
            [self::class, 'render']
        );
    }

    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
        echo '<p>' . esc_html__('Basic traffic and engagement metrics will appear here.', 'artpulse') . '</p>';
    }
}

OrgAnalyticsWidget::register();

