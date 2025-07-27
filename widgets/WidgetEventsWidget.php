<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Wrapper widget for Upcoming Events.
 */
class WidgetEventsWidget {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void {
        wp_add_dashboard_widget('widget_events', __('Upcoming Events', 'artpulse'), [self::class, 'render']);
    }

    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
        echo ap_widget_events([]);
    }
}

WidgetEventsWidget::register();
