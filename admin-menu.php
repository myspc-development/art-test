<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_submenu_page(
        'artpulse',
        'Dashboard Widget Matrix',
        'Dashboard Widget Matrix',
        'manage_options',
        'artpulse-widget-matrix',
        'render_widget_matrix_page'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'artpulse_page_artpulse-widget-matrix') {
        return;
    }
    wp_enqueue_script(
        'ap-widget-matrix',
        plugins_url('dist/widget-matrix.js', ARTPULSE_PLUGIN_FILE),
        ['react', 'react-dom'],
        '1.0.0',
        true
    );
});

function render_widget_matrix_page() {
    echo '<div id="ap-widget-matrix-root"></div>';
    wp_enqueue_script('ap-widget-matrix');
    wp_localize_script('ap-widget-matrix', 'APWidgetMatrix', [
        'root'  => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}


