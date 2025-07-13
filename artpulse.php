<?php
if (!defined('ABSPATH')) { exit; }
/**
 * Plugin loader with version migration.
 */
require_once __DIR__ . '/artpulse-management.php';
require_once __DIR__ . '/includes/db-schema.php';

register_activation_hook(__FILE__, function () {
    $settings = get_option('artpulse_settings', []);
    $settings = array_merge(artpulse_get_default_settings(), $settings);
    update_option('artpulse_settings', $settings);
});

// Setup monetization tables on activation
register_activation_hook(__FILE__, 'ArtPulse\\DB\\create_monetization_tables');

// Optional manual repair: create tables via ?repair_artpulse_db
if (current_user_can('administrator') && isset($_GET['repair_artpulse_db'])) {
    ArtPulse\DB\create_monetization_tables();
    echo '✅ ArtPulse DB tables created.';
}

// Load translations at the proper time
add_action('init', function () {
    load_plugin_textdomain('artpulse', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Ensure tables stay up to date when plugin updates
add_action('plugins_loaded', function () {
    $current = get_option('artpulse_db_version', '0.0.0');
    if (defined('ARTPULSE_VERSION') && version_compare($current, ARTPULSE_VERSION, '<')) {
        ArtPulse\DB\create_monetization_tables();
        update_option('artpulse_db_version', ARTPULSE_VERSION);
    }
});
