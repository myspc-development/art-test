<?php
namespace ArtPulse\Admin\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Dashboard widget allowing organizations to manage outbound webhooks.
 */
class WebhooksWidget
{
    public static function register(): void
    {
        add_action('artpulse_register_dashboard_widget', [self::class, 'register_widget']);
    }

    public static function register_widget(): void
    {
        DashboardWidgetRegistry::register(
            'webhooks',
            __('Webhooks', 'artpulse'),
            'admin-links',
            __('Manage outbound webhooks for automation.', 'artpulse'),
            [self::class, 'render'],
            ['roles' => ['organization']]
        );
    }

    public static function render(): void
    {
        if (function_exists('ap_widget_webhooks')) {
            echo ap_widget_webhooks();
        } else {
            echo '<p>' . esc_html__('Webhook management unavailable.', 'artpulse') . '</p>';
        }
    }
}
