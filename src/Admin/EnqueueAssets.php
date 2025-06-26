<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\Plugin;

class EnqueueAssets {

    public static function register() {
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_assets']);
        add_action('enqueue_block_editor_styles', [self::class, 'enqueue_block_editor_styles']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend']);
    }

    public static function enqueue_block_editor_assets() {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return;
        }

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);

        // Sidebar taxonomy selector script
        $sidebar_script_path = $plugin_dir . '/assets/js/sidebar-taxonomies.js';
        $sidebar_script_url = $plugin_url . '/assets/js/sidebar-taxonomies.js';
        if (file_exists($sidebar_script_path)) {
            wp_enqueue_script(
                'artpulse-taxonomy-sidebar',
                $sidebar_script_url,
                ['wp-edit-post', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins'],
                filemtime($sidebar_script_path)
            );
        }

        // Advanced taxonomy filter block script
        $advanced_script_path = $plugin_dir . '/assets/js/advanced-taxonomy-filter-block.js';
        $advanced_script_url = $plugin_url . '/assets/js/advanced-taxonomy-filter-block.js';
        if (file_exists($advanced_script_path)) {
            wp_enqueue_script(
                'artpulse-advanced-taxonomy-filter-block',
                $advanced_script_url,
                ['wp-blocks', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins'],
                filemtime($advanced_script_path)
            );
        }

        // Filtered list shortcode block script
        $filtered_list_path = $plugin_dir . '/assets/js/filtered-list-shortcode-block.js';
        $filtered_list_url = $plugin_url . '/assets/js/filtered-list-shortcode-block.js';
        if (file_exists($filtered_list_path)) {
            wp_enqueue_script(
                'artpulse-filtered-list-shortcode-block',
                $filtered_list_url,
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-compose', 'wp-plugins'],
                filemtime($filtered_list_path)
            );
        }

        // AJAX taxonomy filter block script
        $ajax_filter_script_path = $plugin_dir . '/assets/js/ajax-filter-block.js';
        $ajax_filter_script_url = $plugin_url . '/assets/js/ajax-filter-block.js';
        if (file_exists($ajax_filter_script_path)) {
            wp_enqueue_script(
                'artpulse-ajax-filter-block',
                $ajax_filter_script_url,
                ['wp-blocks', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins'],
                filemtime($ajax_filter_script_path)
            );
        }
    }

    public static function enqueue_block_editor_styles() {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return;
        }

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $style_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/css/editor-styles.css';
        $style_url = $plugin_url . '/assets/css/editor-styles.css';

        if (file_exists($style_path)) {
            wp_enqueue_style(
                'artpulse-editor-styles',
                $style_url,
                [],
                filemtime($style_path)
            );
        }
    }

    public static function enqueue_admin() {
        $screen = get_current_screen();
        if (!isset($screen->id)) return;

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);

        if ($screen->base === 'artpulse-settings_page_artpulse-engagement') {
            $custom_js_path = $plugin_dir . '/assets/js/ap-engagement-dashboard.js';
            $custom_js_url = $plugin_url . '/assets/js/ap-engagement-dashboard.js';
            if (file_exists($custom_js_path)) {
                wp_enqueue_script(
                    'ap-engagement-dashboard',
                    $custom_js_url,
                    [],
                    filemtime($custom_js_path),
                    true
                );
            }
        }

        if (
            ($screen->base === 'toplevel_page_artpulse-settings' && ($_GET['tab'] ?? '') === 'import_export') ||
            $screen->base === 'artpulse-settings_page_artpulse-import-export'
        ) {
            wp_enqueue_script('papaparse', 'https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js', [], null, true);
            $import_path = $plugin_dir . '/assets/js/ap-csv-import.js';
            $import_url  = $plugin_url . '/assets/js/ap-csv-import.js';
            if (file_exists($import_path)) {
                wp_enqueue_script(
                    'ap-csv-import',
                    $import_url,
                    ['papaparse', 'wp-api-fetch'],
                    filemtime($import_path),
                    true
                );
                wp_localize_script('ap-csv-import', 'APCSVImport', [
                    'endpoint'        => esc_url_raw(rest_url('artpulse/v1/import')),
                    'templateBase'    => esc_url_raw(rest_url('artpulse/v1/import-template')),
                    'nonce'           => wp_create_nonce('wp_rest'),
                ]);
            }
        }

        // Enqueue Core-specific admin assets (if not already enqueued on frontend)
        // Check if they are already enqueued in the frontend, if not, enqueue them here
        if (!wp_script_is('ap-user-dashboard-js', 'enqueued')) {
            wp_enqueue_style(
                'ap-style',
                $plugin_url . '/assets/css/ap-style.css',
                [],
                '1.0.0'
            );
            wp_enqueue_script(
                'ap-user-dashboard-js',
                $plugin_url . '/assets/js/ap-user-dashboard.js',
                [],
                '1.0.0',
                true
            );
        }
         if (!wp_script_is('ap-analytics', 'enqueued')) {
             wp_enqueue_script(
                'ap-analytics-js',
                $plugin_url . '/assets/js/ap-analytics.js',
                [],
                '1.0.0',
                true
            );
         }
        if (!wp_script_is('ap-my-follows', 'enqueued')) {
             wp_enqueue_script(
                'ap-my-follows-js',
                $plugin_url . '/assets/js/ap-my-follows.js',
                [],
                '1.0.0',
                true
            );
         }
    }

    public static function enqueue_frontend() {
        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);

        wp_enqueue_script(
            'ap-membership-account-js',
            $plugin_url . '/assets/js/ap-membership-account.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-favorites-js',
            $plugin_url . '/assets/js/ap-favorites.js',
            [],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-notifications-js',
            $plugin_url . '/assets/js/ap-notifications.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-notifications-js', 'APNotifications', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'ap-submission-form-js',
            $plugin_url . '/assets/js/ap-submission-form.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-submission-form-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'ap-org-submission-js',
            $plugin_url . '/assets/js/ap-org-submission.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-org-submission-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'ap-address-autocomplete',
            $plugin_url . '/assets/js/address-autocomplete.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-address-autocomplete', 'APLocation', [
            'countriesUrl'     => $plugin_url . '/data/countries.json',
            'statesUrl'        => $plugin_url . '/data/states.json',
            'citiesUrl'        => $plugin_url . '/data/cities.json',
            'geonamesEndpoint' => esc_url_raw(rest_url('artpulse/v1/location/geonames')),
            'googleEndpoint'   => esc_url_raw(rest_url('artpulse/v1/location/google')),
        ]);

        wp_enqueue_script(
            'ap-google-places',
            $plugin_url . '/assets/js/google-places-autocomplete.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        if (!ap_styles_disabled()) {
            wp_enqueue_style(
                'ap-style',
                $plugin_url . '/assets/css/ap-style.css',
                [],
                '1.0.0'
            );
        }

        // Enqueue user dashboard styles (Frontend)

        wp_enqueue_script(
            'ap-analytics',
            $plugin_url . '/assets/js/ap-analytics.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-directory',
            $plugin_url . '/assets/js/ap-directory.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-my-follows',
            $plugin_url . '/assets/js/ap-my-follows.js',
            ['jquery'],
            '1.0.0',
            true
        );

        $org_dashboard_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-org-dashboard.js';
        $org_dashboard_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-org-dashboard.js';
        if (file_exists($org_dashboard_path)) {
            wp_enqueue_script(
                'ap-org-dashboard',
                $org_dashboard_url,
                ['jquery'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-org-dashboard', 'APOrgDashboard', [
                'ajax_url'     => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('ap_org_dashboard_nonce'),
                'eventFormUrl' => Plugin::get_event_submission_url(),
                'rest_root'    => esc_url_raw(rest_url()),
                'rest_nonce'   => wp_create_nonce('wp_rest'),
            ]);
        }

        $artist_dashboard_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-artist-dashboard.js';
        $artist_dashboard_url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-artist-dashboard.js';
        if (file_exists($artist_dashboard_path)) {
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
            wp_enqueue_script('sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', [], null, true);
            wp_enqueue_script(
                'ap-artist-dashboard',
                $artist_dashboard_url,
                ['jquery', 'chart-js', 'sortable-js'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-artist-dashboard', 'APArtistDashboard', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ap_artist_dashboard_nonce'),
            ]);
        }

        wp_enqueue_script(
            'ap-user-dashboard-js',
            $plugin_url . '/assets/js/ap-user-dashboard.js',
            ['jquery'],
            '1.0.0',
            true
        );


        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['service_worker_enabled'])) {
            wp_enqueue_script(
                'ap-sw-loader',
                $plugin_url . '/assets/js/sw-loader.js',
                [],
                '1.0.0',
                true
            );
            wp_localize_script('ap-sw-loader', 'APServiceWorker', [
                'url'     => $plugin_url . '/assets/js/service-worker.js',
                'enabled' => true,
            ]);
        }
    }
}