<?php
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function () {
    add_menu_page(
        'Widget Layout Editor',
        'Widget Editor',
        'manage_options',
        'artpulse-widget-editor',
        'artpulse_render_widget_editor_page',
        'dashicons-layout',
        80
    );
});

function artpulse_render_widget_editor_page() {
    echo '<div id="admin-dashboard-widgets-editor"></div>';
}

add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'toplevel_page_artpulse-widget-editor') {
        return;
    }

    $handle = 'ap-dashboard-widgets-editor';
    wp_enqueue_script(
        $handle,
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/dist/admin-dashboard-widgets-editor.js',
        ['wp-element'],
        '1.0.0',
        true
    );

    wp_localize_script($handle, 'APDashboardWidgetsEditor', [
        'widgets' => artpulse_get_dashboard_widgets(),
        'config'  => [
            'can_edit' => current_user_can('edit_dashboard'),
        ],
        'roles'   => artpulse_get_dashboard_roles(),
    ]);

    wp_enqueue_script(
        'artpulse-react-editor',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/widget-editor.js',
        [$handle],
        null,
        true
    );
    wp_localize_script('artpulse-react-editor', 'ArtPulseWidgetData', [
        'nonce'  => wp_create_nonce('wp_rest'),
        'layout' => get_user_meta(get_current_user_id(), 'artpulse_dashboard_layout', true) ?: []
    ]);
});
