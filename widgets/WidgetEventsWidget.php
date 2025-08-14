<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if (!defined('ABSPATH')) { exit; }
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

/**
 * Wrapper widget for Upcoming Events.
 */

class WidgetEventsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'widget_events',
            'Upcoming Events',
            'calendar',
            'Upcoming events for your organization.',
            [self::class, 'render'],
            [ 'roles' => ['member', 'organization'] ]
        );

        // Legacy alias used in older configs.
        if (!DashboardWidgetRegistry::exists('widget_widget_events')) {
            DashboardWidgetRegistry::register(
                'widget_widget_events',
                'Upcoming Events (Legacy)',
                'calendar',
                'Upcoming events for your organization.',
                [self::class, 'render'],
                [ 'roles' => ['member', 'organization'] ]
            );
        }
    }

      public static function render(): string {
          ob_start();
          echo '<div data-widget-id="widget_events">';
          echo ap_widget_events([]);
          echo '</div>';
          return ob_get_clean();
      }
}

WidgetEventsWidget::register();
