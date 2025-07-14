<?php
/**
 * Register global plugin settings.
 */
function artpulse_register_settings() {
    register_setting('artpulse_settings_group', 'artpulse_settings');
}
add_action('admin_init', 'artpulse_register_settings');

function artpulse_get_default_settings(): array {
    return [
        'theme'            => 'default',
        'enable_reporting' => true,
        'admin_email'      => get_option('admin_email'),
    ];
}
