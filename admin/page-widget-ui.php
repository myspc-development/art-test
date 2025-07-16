<?php
if (!defined('ABSPATH')) {
    exit;
}

function artpulse_admin_assets($hook) {
    if ($hook !== 'toplevel_page_artpulse_dashboard') {
        return;
    }
    wp_enqueue_script(
        'artpulse-editor',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'js/widget-editor.js',
        ['jquery', 'jquery-ui-sortable'],
        null,
        true
    );
    wp_enqueue_style(
        'artpulse-style',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'css/editor-style.css'
    );
    wp_localize_script('artpulse-editor', 'APWidgetEditor', [
        'nonce' => wp_create_nonce('ap_save_role_layout'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'role' => 'administrator',
        'show' => __('Show', 'artpulse'),
        'hide' => __('Hide', 'artpulse')
    ]);
}
add_action('admin_enqueue_scripts', 'artpulse_admin_assets');

function artpulse_add_admin_page() {
    add_menu_page(
        __('ArtPulse Dashboard Widgets', 'artpulse'),
        __('Dashboard Widgets', 'artpulse'),
        'manage_options',
        'artpulse_dashboard',
        'artpulse_render_dashboard_ui',
        'dashicons-welcome-widgets-menus'
    );
}
add_action('admin_menu', 'artpulse_add_admin_page');

function artpulse_render_dashboard_ui() {
    echo '<div id="artpulse-widget-editor-root"></div>';
    echo '<div id="ap-widget-notice" role="alert" aria-live="polite" class="hidden"></div>';
}
