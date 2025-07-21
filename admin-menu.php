<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'ap_widget_matrix_register_page');

function ap_widget_matrix_register_page(): void
{
    add_submenu_page(
        'artpulse-settings',
        __('Dashboard Widget Matrix', 'artpulse'),
        __('Dashboard Widget Matrix', 'artpulse'),
        'manage_options',
        'artpulse-widget-matrix',
        'ap_render_widget_matrix_page'
    );
}

// Redirect direct slug path to the admin.php endpoint
add_action('admin_init', function () {
    $uri  = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH);
    if ($path === '/wp-admin/artpulse-widget-matrix') {
        wp_safe_redirect(admin_url('admin.php?page=artpulse-widget-matrix'));
        exit;
    }
});

add_action('admin_enqueue_scripts', 'ap_widget_matrix_enqueue');

function ap_widget_matrix_enqueue(string $hook): void
{
    if (strpos($hook, 'artpulse-widget-matrix') === false) {
        return;
    }
    wp_enqueue_script(
        'ap-widget-matrix',
        plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'dist/widget-matrix.js',
        ['react', 'react-dom', 'wp-element'],
        '1.0.0',
        true
    );
    wp_localize_script('ap-widget-matrix', 'APWidgetMatrix', [
        'root'  => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}

function ap_render_widget_matrix_page(): void
{
    echo '<div id="ap-widget-matrix-root"></div>';
    wp_enqueue_script('ap-widget-matrix');
}


