<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages front-end asset loading for the plugin.
 */
class ArtPulse_Assets
{
    /**
     * Register hooks.
     */
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    /**
     * Conditionally enqueue scripts and styles for public pages.
     */
    public static function enqueue_assets(): void
    {
        if (is_page('dashboard') || is_page_template('page-dashboard.php')) {
            $base = plugin_dir_url(dirname(__FILE__));
            wp_enqueue_style('ap-dashboard', $base . 'assets/css/dashboard.css', [], defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false);
            wp_enqueue_script('ap-dashboard', $base . 'assets/js/dashboard.js', [], defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false, true);
            if (function_exists('wp_set_script_translations')) {
                wp_set_script_translations('ap-dashboard', 'artpulse', plugin_dir_path(dirname(__FILE__)) . 'languages');
            }
        }
    }
}
