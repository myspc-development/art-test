<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Dashboard panel listing widget manifest status.
 */
class WidgetManifestPanelWidget {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void {
        wp_add_dashboard_widget('ap_widget_manifest_panel', __('Widget Manifest', 'artpulse'), [self::class, 'render']);
    }

    public static function render(): void {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
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
