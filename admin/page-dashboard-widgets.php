<?php
use ArtPulse\Core\DashboardWidgetRegistry;

add_action('admin_menu', function() {
    add_submenu_page(
        'artpulse-dashboard',
        __('Widget Manager', 'artpulse'),
        __('Widgets', 'artpulse'),
        'manage_options',
        'artpulse-dashboard-widgets',
        'ap_render_dashboard_widget_manager'
    );
});

function ap_render_dashboard_widget_manager(): void {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'artpulse'));
    }
    $widgets = DashboardWidgetRegistry::get_all();
    $config  = get_option('ap_dashboard_widget_config', []);

    echo '<div class="wrap"><h1>' . esc_html__('Dashboard Widgets', 'artpulse') . '</h1>';
    echo '<table class="widefat striped"><thead><tr>';
    echo '<th>' . esc_html__('ID', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Title', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Roles', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Enabled?', 'artpulse') . '</th>';
    echo '</tr></thead><tbody>';

    foreach ($widgets as $id => $def) {
        $roles  = isset($def['roles']) ? (array) $def['roles'] : [];
        $checks = [];
        foreach ($roles as $r) {
            $enabled = empty($config[$r]) || in_array($id, (array) $config[$r], true);
            $checks[] = '<label><input type="checkbox" disabled ' . checked($enabled, true, false) . ' /> ' . esc_html($r) . '</label>';
        }
        echo '<tr>';
        echo '<td>' . esc_html($id) . '</td>';
        $title = $def['title'] ?? ($def['label'] ?? '');
        echo '<td>' . esc_html($title) . '</td>';
        echo '<td>' . esc_html(implode(', ', $roles)) . '</td>';
        echo '<td>' . implode(' ', $checks) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
