<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    if (!ap_rest_route_registered('artpulse/v1', '/status')) {
        register_rest_route('artpulse/v1', '/status', [
        'methods'             => 'GET',
        'callback'            => 'ap_get_system_status',
        'permission_callback' => function () {
            if (!current_user_can('manage_options')) {
                return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
            }
            return true;
        },
    ]);
    }
});

function ap_get_system_status() {
    $plugin_version = defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : '1.0.0';
    $db_version     = get_option('artpulse_db_version', '0.0.0');
    $cache          = (defined('WP_CACHE') && WP_CACHE) ? 'Enabled' : 'Disabled';
    $debug          = defined('WP_DEBUG') && WP_DEBUG;

    return [
        'plugin_version' => $plugin_version,
        'db_version'     => $db_version,
        'cache'          => $cache,
        'debug'          => $debug,
    ];
}
