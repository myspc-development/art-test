<?php
namespace ArtPulse\Admin\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Debug widget displaying widget status information.
 */
class WidgetStatusPanelWidget
{
    public static function register(): void
    {
        add_action('artpulse_register_dashboard_widget', [self::class, 'register_widget']);
    }

    public static function register_widget(): void
    {
        DashboardWidgetRegistry::register(
            'ap_widget_status_panel',
            __('Widget Status Panel', 'artpulse'),
            'info',
            __('Registered and missing widgets.', 'artpulse'),
            [self::class, 'render'],
            ['roles' => ['administrator']]
        );
    }

    public static function render(): void
    {
        echo '<p>' . esc_html__('Widget status placeholder.', 'artpulse') . '</p>';
    }
}
