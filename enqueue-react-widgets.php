<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'ap-react-widgets',
        plugins_url('assets/js/react-widgets.bundle.js', ARTPULSE_PLUGIN_FILE),
        ['react', 'react-dom', 'wp-api-fetch'],
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'ap-widgets',
        plugins_url('assets/js/widgets.bundle.js', ARTPULSE_PLUGIN_FILE),
        ['react', 'react-dom', 'wp-api-fetch'],
        '1.0.0',
        true
    );

    wp_localize_script('ap-react-widgets', 'APChat', [
        'apiRoot'  => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
        'loggedIn' => is_user_logged_in(),
    ]);
});
