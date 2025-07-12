<?php
/**
 * Plugin loader with version migration.
 */
require_once __DIR__ . '/artpulse-management.php';

register_activation_hook(__FILE__, function () {
    $settings = get_option('artpulse_settings', []);
    $settings = array_merge(artpulse_get_default_settings(), $settings);
    update_option('artpulse_settings', $settings);
});
