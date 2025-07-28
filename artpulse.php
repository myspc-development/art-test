<?php
if (!defined('ABSPATH')) { exit; }
/**
 * Plugin loader with version migration.
 */
// Core management bootstrap is loaded by artpulse-management.php
require_once __DIR__ . '/includes/db-schema.php';
require_once __DIR__ . '/includes/avatar-https-fix.php';

// Development helpers
if (defined('WP_DEBUG') && WP_DEBUG) {
    $dev_file = __DIR__ . '/includes/dev/debug-rest.php';
    if (file_exists($dev_file)) {
        require_once $dev_file;
    }
}

register_activation_hook(ARTPULSE_PLUGIN_FILE, function () {
    $settings = get_option('artpulse_settings', []);
    $settings = array_merge(artpulse_get_default_settings(), $settings);
    update_option('artpulse_settings', $settings);
});

// Setup monetization tables on activation
register_activation_hook(ARTPULSE_PLUGIN_FILE, 'ArtPulse\\DB\\create_monetization_tables');

// Optional manual repair: create tables via ?repair_artpulse_db
add_action('plugins_loaded', function () {
    if (current_user_can('administrator') && isset($_GET['repair_artpulse_db'])) {
        ArtPulse\DB\create_monetization_tables();
        esc_html_e('✅ ArtPulse DB tables created.', 'artpulse');
    }
});

// Load translations at the proper time
function ap_load_textdomain() {
    load_plugin_textdomain('artpulse', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'ap_load_textdomain');

// Ensure tables stay up to date when plugin updates
add_action('plugins_loaded', function () {
    $current = get_option('artpulse_db_version', '0.0.0');
    if (defined('ARTPULSE_VERSION') && version_compare($current, ARTPULSE_VERSION, '<')) {
        ArtPulse\DB\create_monetization_tables();
        update_option('artpulse_db_version', ARTPULSE_VERSION);
    }
});

add_action('plugins_loaded', function () {
    if (defined('ARTPULSE_VERSION') && get_option('artpulse_version') !== ARTPULSE_VERSION) {
        ArtPulse\DB\create_monetization_tables();
        update_option('artpulse_version', ARTPULSE_VERSION);
    }
});

// Register Diagnostics admin page
add_action('admin_menu', function () {
add_menu_page(
        __('ArtPulse Diagnostics', 'artpulse'),
        __('AP Diagnostics', 'artpulse'),
        'manage_options',
        'ap-diagnostics',
        'ap_diagnostics_page_loader',
        'dashicons-admin-tools',
        99
    );
});

function ap_diagnostics_page_loader() {
    $path = plugin_dir_path(__FILE__) . 'admin/ap-diagnostics-page.php';
    if (file_exists($path)) {
        include $path;
    } else {
        echo '<div class="notice notice-error"><p>' .
            esc_html__('Diagnostics file not found.', 'artpulse') .
            '</p></div>';
    }
}

// AJAX handler for diagnostics test
add_action('wp_ajax_ap_ajax_test', function () {
    check_ajax_referer('ap_diagnostics_test', 'nonce');

    wp_send_json_success([
        'message' => __('AJAX is working, nonce is valid, and you are authenticated.', 'artpulse')
    ]);
});
