<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Wrapper widget for Upcoming Events.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetEventsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'widget_events',
            __('Upcoming Events', 'artpulse'),
            'calendar',
            __('Upcoming events for your organization.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['member', 'organization'] ]
        );
    }

    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
        echo ap_widget_events([]);
    }
}

WidgetEventsWidget::register();
