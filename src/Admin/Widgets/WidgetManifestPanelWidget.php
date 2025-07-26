<?php
namespace ArtPulse\Admin\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Debug widget listing the widget manifest.
 */
class WidgetManifestPanelWidget
{
    public static function register(): void
    {
        add_action('artpulse_register_dashboard_widget', [self::class, 'register_widget']);
    }

    public static function register_widget(): void
    {
        DashboardWidgetRegistry::register(
            'ap_widget_manifest_panel',
            __('Widget Manifest', 'artpulse'),
            'admin-page',
            __('List of available widgets.', 'artpulse'),
            [self::class, 'render'],
            ['roles' => ['administrator']]
        );
    }

    public static function render(): void
    {
        echo '<p>' . esc_html__('Widget manifest placeholder.', 'artpulse') . '</p>';
    }
}
