<?php
namespace ArtPulse\Core;

class AdminDashboard
{
    public static function register()
    {
        add_action('admin_menu', [ self::class, 'addMenus' ]);
        add_action('admin_enqueue_scripts', [ self::class, 'enqueue' ]);
    }

    public static function addMenus()
    {
        add_menu_page(
            __('ArtPulse', 'artpulse'),
            __('ArtPulse', 'artpulse'),
            'manage_options',
            'artpulse-dashboard',
            [ self::class, 'renderDashboard' ],
            'dashicons-art', // choose an appropriate dashicon
            60
        );
        add_submenu_page(
            'artpulse-dashboard',
            __('Events','artpulse'),
            __('Events','artpulse'),
            'edit_artpulse_events',
            'edit.php?post_type=artpulse_event'
        );
        add_submenu_page(
            'artpulse-dashboard',
            __('Artists','artpulse'),
            __('Artists','artpulse'),
            'edit_artpulse_artists',
            'edit.php?post_type=artpulse_artist'
        );
        add_submenu_page(
            'artpulse-dashboard',
            __('Artworks','artpulse'),
            __('Artworks','artpulse'),
            'edit_artpulse_artworks',
            'edit.php?post_type=artpulse_artwork'
        );
        add_submenu_page(
            'artpulse-dashboard',
            __('Organizations','artpulse'),
            __('Organizations','artpulse'),
            'edit_artpulse_orgs',
            'edit.php?post_type=artpulse_org'
        );
    }

    public static function enqueue(string $hook): void
    {
        if ($hook !== 'toplevel_page_artpulse-dashboard') {
            return;
        }

        $path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-dashboard.js';
        $url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-dashboard.js';

        if (file_exists($path)) {
            wp_enqueue_script(
                'ap-dashboard-js',
                $url,
                ['wp-element', 'wp-api-fetch'],
                filemtime($path),
                true
            );
            wp_script_add_data('ap-dashboard-js', 'type', 'module');

            wp_localize_script(
                'ap-dashboard-js',
                'ArtPulseDashboardData',
                [
                    'nonce'    => wp_create_nonce('wp_rest'),
                    'rest_url' => rest_url('artpulse/v1/'),
                ]
            );
        }
    }

    public static function renderDashboard()
    {
        echo '<div class="wrap"><h1>' . esc_html__('ArtPulse Dashboard','artpulse') . '</h1>';
        echo '<div id="ap-dashboard-root"></div></div>';
        wp_enqueue_script('ap-dashboard-js');
    }
}
