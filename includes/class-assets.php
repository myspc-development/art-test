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

            wp_enqueue_style(
                'ap-dashboard',
                $base . 'assets/css/dashboard.css',
                [],
                defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false
            );
            wp_enqueue_style(
                'ap-calendar',
                $base . 'assets/css/calendar.css',
                [],
                defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false
            );

            wp_enqueue_script(
                'ap-user-dashboard',
                $base . 'assets/js/ap-user-dashboard.js',
                ['wp-i18n'],
                defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false,
                true
            );
            wp_script_add_data('ap-user-dashboard', 'type', 'module');

            $user = wp_get_current_user();
            $boot = [
                'restRoot' => esc_url_raw(rest_url()),
                'restNonce' => wp_create_nonce('wp_rest'),
                'currentUser' => [
                    'id' => $user->ID,
                    'displayName' => $user->display_name,
                    'roles' => $user->roles,
                ],
                'i18n' => [
                    'Confirm' => __('Confirm', 'artpulse'),
                    'Cancel' => __('Cancel', 'artpulse'),
                    'OK' => __('OK', 'artpulse'),
                ],
                'routes' => [
                    'overview' => '#overview',
                    'calendar' => '#calendar',
                    'favorites' => '#favorites',
                    'rsvps' => '#rsvps',
                    'events' => '#events',
                    'analytics' => '#analytics',
                    'portfolio' => '#portfolio',
                    'artworks' => '#artworks',
                    'settings' => '#settings',
                ],
                'featureFlags' => [],
            ];
            wp_localize_script('ap-user-dashboard', 'ARTPULSE_BOOT', $boot);

            if (function_exists('wp_set_script_translations')) {
                wp_set_script_translations('ap-user-dashboard', 'artpulse', plugin_dir_path(dirname(__FILE__)) . 'languages');
            }
        }
    }
}
