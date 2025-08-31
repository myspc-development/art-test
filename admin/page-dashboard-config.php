<?php
if (!defined('ABSPATH')) {
    exit;
}

function ap_render_dashboard_config_page(): void {
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'artpulse'));
    }

    $handle = 'ap-widget-matrix';
    wp_enqueue_script(
        $handle,
        plugins_url('/assets/js/ap-widget-matrix.js', ARTPULSE_PLUGIN_FILE),
        ['react', 'react-dom', 'wp-element', 'wp-i18n'],
        filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-widget-matrix.js'),
        true
    );

    wp_localize_script(
        $handle,
        'APWidgetMatrix',
        [
            'endpoint' => rest_url('artpulse/v1/dashboard-config'),
            'nonce'    => wp_create_nonce('wp_rest'),
            'apNonce'  => wp_create_nonce('ap_dashboard_config'),
        ]
    );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Dashboard Configuration', 'artpulse'); ?></h1>
        <div id="ap-widget-matrix-root"></div>
    </div>
    <?php
}

ap_render_dashboard_config_page();

