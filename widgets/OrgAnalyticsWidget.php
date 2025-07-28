<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;

if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Simple dashboard widget showing basic organization analytics.
 */

class OrgAnalyticsWidget {
    public static function can_view( int $user_id ): bool {
        $role = DashboardController::get_role( $user_id );
        return $role === 'organization' && user_can( $user_id, 'view_analytics' );
    }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'chart-bar',
            __('Basic traffic and engagement metrics.', 'artpulse'),
            [self::class, 'render'],
            [
                'roles'      => ['organization'],
                'capability' => 'view_analytics',
                'section'    => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'artpulse_analytics_widget'; }
    public static function get_title(): string { return __('Organization Analytics', 'artpulse'); }
    public static function get_section(): string { return 'insights'; }

    public static function render( int $user_id ): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
        if ( ! self::can_view( $user_id ) ) {
            echo '<p class="ap-widget-no-access">' . esc_html__("You do not have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo '<p>' . esc_html__('Basic traffic and engagement metrics will appear here.', 'artpulse') . '</p>';
    }
}

OrgAnalyticsWidget::register();

