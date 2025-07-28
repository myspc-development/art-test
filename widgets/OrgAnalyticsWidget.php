<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Simple dashboard widget showing basic organization analytics.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class OrgAnalyticsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'artpulse_analytics_widget',
            __('Organization Analytics', 'artpulse'),
            'chart-bar',
            __('Basic traffic and engagement metrics.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['organization'], 'capability' => 'view_analytics' ]
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

