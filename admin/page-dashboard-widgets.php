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
    $defs = DashboardWidgetRegistry::get_definitions(true);
    echo '<div class="wrap"><h1>' . esc_html__('Dashboard Widgets', 'artpulse') . '</h1>';
    echo '<table class="widefat striped"><thead><tr>';
    echo '<th>' . esc_html__('ID', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Title', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Roles', 'artpulse') . '</th>';
    echo '<th>' . esc_html__('Template', 'artpulse') . '</th>';
    echo '</tr></thead><tbody>';
    foreach ($defs as $id => $def) {
        $roles = isset($def['roles']) ? implode(', ', (array) $def['roles']) : '';
        $template = $def['template'] ?? '';
        echo '<tr>';
        echo '<td>' . esc_html($id) . '</td>';
        echo '<td>' . esc_html($def['name']) . '</td>';
        echo '<td>' . esc_html($roles) . '</td>';
        echo '<td>' . esc_html($template) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
