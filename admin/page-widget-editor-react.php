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
    echo '<div id="artpulse-widget-editor-root"></div>';
}

add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'toplevel_page_artpulse-widget-editor') {
        return;
    }
    wp_enqueue_script(
        'artpulse-react-editor-core',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/dist/admin-dashboard-widgets-editor.js',
        ['wp-element', 'wp-data'],
        null,
        true
    );
    wp_enqueue_script(
        'artpulse-react-editor',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/widget-editor.js',
        ['artpulse-react-editor-core'],
        null,
        true
    );
    wp_localize_script('artpulse-react-editor', 'ArtPulseWidgetData', [
        'nonce'  => wp_create_nonce('wp_rest'),
        'layout' => get_user_meta(get_current_user_id(), 'artpulse_dashboard_layout', true) ?: []
    ]);
});
