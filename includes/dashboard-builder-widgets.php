<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetRegistry as CoreDashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistryLoader;
use ArtPulse\Admin\Widgets\WidgetStatusPanelWidget;
use ArtPulse\Admin\Widgets\WidgetManifestPanelWidget;

// Load and register widgets defined in the configuration file.
add_action('artpulse_register_dashboard_widget', [WidgetRegistryLoader::class, 'register_widgets']);

function ap_register_dashboard_builder_widget_map(): void {
    $config = WidgetRegistryLoader::get_config();

    global $ap_widget_source_map, $ap_widget_status;
    $ap_widget_source_map = [];
    $ap_widget_status     = [
        'registered'   => [],
        'missing'      => [],
        'unregistered' => [],
    ];

    foreach ($config as $id => $data) {
        $roles = $data['roles'] ?? [];
        foreach ($roles as $role) {
            $ap_widget_source_map[$role][$id] = $data['class'] ?? ($data['callback'] ?? '');
        }

        $args = [
            'title' => $data['label'] ?? ucwords(str_replace(['_', '-'], ' ', $id)),
            'roles' => $roles,
        ];

        if (isset($data['class']) && method_exists($data['class'], 'render')) {
            $args['render_callback'] = [$data['class'], 'render'];
        } elseif (isset($data['callback'])) {
            $args['render_callback'] = $data['callback'];
        } else {
            $cb = function_exists('render_widget_' . $id) ? 'render_widget_' . $id : '__return_empty_string';
            $args['render_callback'] = $cb;
        }

        if (!DashboardWidgetRegistry::get($id)) {
            DashboardWidgetRegistry::register($id, $args);
        }
    }
}
add_action('init', 'ap_register_dashboard_builder_widget_map', 20);

function ap_register_builder_core_placeholders(): void {
    $config = WidgetRegistryLoader::get_config();
    foreach ($config as $id => $data) {
        $core_id = 'widget_' . $id;
        if (!CoreDashboardWidgetRegistry::get($core_id)) {
            CoreDashboardWidgetRegistry::register_widget($core_id, [
                'label'    => $data['label'] ?? ucwords(str_replace(['_', '-'], ' ', $id)),
                'callback' => 'render_widget_' . $core_id,
                'roles'    => $data['roles'] ?? [],
            ]);
        }
    }
}
add_action('init', 'ap_register_builder_core_placeholders', 25);

if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
    WidgetStatusPanelWidget::register();
    WidgetManifestPanelWidget::register();
}
