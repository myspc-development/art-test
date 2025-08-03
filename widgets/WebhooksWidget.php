<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Dashboard widget allowing organizations to manage outbound webhooks.
 */
class WebhooksWidget {
    /** Register the widget with the registry. */
    public static function register(): void {
        add_action('artpulse_register_dashboard_widget', [self::class, 'register_widget']);
    }

    /** Hook callback for widget registration. */
    public static function register_widget(): void {
        DashboardWidgetRegistry::register(
            'webhooks',
            __('Webhooks', 'artpulse'),
            'admin-links',
            __('Manage outbound webhooks for automation.', 'artpulse'),
            'ap_widget_webhooks',
            [ 'roles' => ['organization'], 'visibility' => 'public' ]
        );
    }
}

WebhooksWidget::register();
