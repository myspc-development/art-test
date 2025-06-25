<?php
/**
 * Plugin Name:     ArtPulse Management
 * Description:     Management plugin for ArtPulse.
 * Version:         1.1.5
 * Author:          craig
 * Text Domain:     artpulse
 * License:         GPL2
 */

use ArtPulse\Core\Plugin;
use ArtPulse\Core\WooCommerceIntegration;
use ArtPulse\Core\Activator;
use ArtPulse\Admin\EnqueueAssets;

// Suppress deprecated notices if WP_DEBUG enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    @ini_set('display_errors', '0');
    @error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// Define ARTPULSE_PLUGIN_FILE constant (THIS IS CRUCIAL - MUST BE DEFINED CORRECTLY)
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __FILE__);
}

// Load Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 🔧 Boot the main plugin class (responsible for registering menus, settings, CPTs, etc.)
$main = new Plugin();

// Optional debug log for class check
if (class_exists(WooCommerceIntegration::class)) {
    error_log('Plugin class loaded successfully');
} else {
    error_log('Failed to load Plugin class');
}

// Instantiate WooCommerce integration (if needed for runtime)
$plugin = new WooCommerceIntegration();

// ✅ Hook for activation
register_activation_hook(__FILE__, function () {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    artpulse_create_custom_table();
    Activator::activate(); // WooCommerceIntegration has no activate() method
});

// ✅ Hook for deactivation
//register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);

// Register REST API routes
add_action('rest_api_init', function () {
    \ArtPulse\Rest\PortfolioRestController::register();
    \ArtPulse\Rest\UserAccountRestController::register();
});

// Register Enqueue Assets
add_action('init', function () {
    EnqueueAssets::register();
});

function artpulse_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'artpulse_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        artist_name varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Enqueue global styles on the frontend.
 */
/**
 * Check if the current post content contains any ArtPulse shortcode.
 *
 * @return bool
 */
function ap_page_has_artpulse_shortcode() {
    if (!is_singular()) {
        return false;
    }

    global $post;

    if (!$post || empty($post->post_content)) {
        return false;
    }

    return strpos($post->post_content, '[ap_') !== false;
}

/**
 * Get the active theme accent color.
 *
 * @return string Hex color string.
 */
function ap_get_accent_color() {
    return get_theme_mod('accent_color', '#0073aa');
}

/**
 * Adjust a hex color brightness by the given percentage.
 *
 * @param string $hex      Base color in hex format.
 * @param float  $percent  Percentage to lighten/darken (-1 to 1).
 * @return string Adjusted hex color.
 */
function ap_adjust_color_brightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) .
               str_repeat(substr($hex, 1, 1), 2) .
               str_repeat(substr($hex, 2, 1), 2);
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, (int) ($r * (1 + $percent))));
    $g = max(0, min(255, (int) ($g * (1 + $percent))));
    $b = max(0, min(255, (int) ($b * (1 + $percent))));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Enqueue the global UI styles on the frontend.
 *
 * By default the styles are only loaded when a page contains an
 * ArtPulse shortcode. Themes or page builders can bypass this detection by
 * filtering {@see 'ap_bypass_shortcode_detection'} and returning true.
 */
function ap_enqueue_global_styles() {
    if (is_admin()) {
        return;
    }

    $bypass = apply_filters('ap_bypass_shortcode_detection', false);

    if ($bypass || ap_page_has_artpulse_shortcode()) {
        wp_enqueue_style(
            'ap-global-ui',
            plugin_dir_url(__FILE__) . 'assets/css/ap-core.css',
            [],
            '1.0'
        );

        $accent = ap_get_accent_color();
        $hover  = ap_adjust_color_brightness($accent, -0.1);
        wp_add_inline_style(
            'ap-global-ui',
            ":root { --ap-primary-color: {$accent}; --ap-primary-hover: {$hover}; }"
        );
    }
}
add_action('wp_enqueue_scripts', 'ap_enqueue_global_styles');

/**
 * Optionally enqueue styles for the admin area.
 *
 * @param string $hook Current admin page hook.
 */
function ap_enqueue_admin_styles($hook) {
    if (strpos($hook, 'artpulse') !== false) {
        wp_enqueue_style(
            'ap-admin-ui',
            plugin_dir_url(__FILE__) . 'assets/css/ap-core.css',
            [],
            '1.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'ap_enqueue_admin_styles');
