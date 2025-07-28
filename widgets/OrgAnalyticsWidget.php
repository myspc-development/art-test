<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Simple dashboard widget showing basic organization analytics.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class OrgAnalyticsWidget {
    public static function can_view(): bool {
        $role = \ArtPulse\Core\DashboardController::get_role( get_current_user_id() );
        return $role === 'organization' && current_user_can( 'view_analytics' );
    }

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
        if ( ! self::can_view() ) {
            echo '<p class="ap-widget-no-access">' . esc_html__("You do not have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo '<p>' . esc_html__('Basic traffic and engagement metrics will appear here.', 'artpulse') . '</p>';
    }
}

OrgAnalyticsWidget::register();

