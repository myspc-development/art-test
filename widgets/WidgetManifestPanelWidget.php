<?php
namespace ArtPulse\Widgets;

if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Dashboard panel listing widget manifest status.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetManifestPanelWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'widget_manifest_panel',
            __('Widget Manifest', 'artpulse'),
            'list-view',
            __('Status of registered widgets.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['administrator'] ]
        );
    }

    public static function render(): void {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        if (!current_user_can('manage_options')) {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        $path = dirname(__DIR__) . '/widget-manifest.json';
        if (!file_exists($path)) {
            echo esc_html__('Manifest not found.', 'artpulse');
            return;
        }
        $data = json_decode(file_get_contents($path), true);
        if (!$data) {
            echo esc_html__('Manifest empty.', 'artpulse');
            return;
        }
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('ID', 'artpulse') . '</th><th>' . esc_html__('Roles', 'artpulse') . '</th><th>' . esc_html__('Status', 'artpulse') . '</th></tr></thead><tbody>';
        foreach ($data as $id => $info) {
            $roles = implode(', ', $info['roles']);
            $status = esc_html($info['status']);
            echo '<tr><td>' . esc_html($id) . '</td><td>' . esc_html($roles) . '</td><td>' . $status . '</td></tr>';
        }
        echo '</tbody></table>';
    }
}

WidgetManifestPanelWidget::register();
