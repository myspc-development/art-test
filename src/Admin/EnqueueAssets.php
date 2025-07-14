<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\Plugin;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

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

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);

        if ($screen->id === 'edit-artpulse_event') {
            $script = $plugin_dir . '/assets/js/ap-event-gallery.js';
            if (file_exists($script)) {
                wp_enqueue_script(
                    'ap-event-gallery',
                    $plugin_url . '/assets/js/ap-event-gallery.js',
                    ['jquery', 'jquery-ui-sortable'],
                    filemtime($script),
                    true
                );
                wp_localize_script('ap-event-gallery', 'APEvtGallery', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('ap_event_gallery_nonce'),
                ]);
            }
        }

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

        $sidebar_plugin_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/admin/sidebar.js';
        $sidebar_plugin_url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'admin/sidebar.js';
        if (file_exists($sidebar_plugin_path)) {
            wp_enqueue_script(
                'artpulse-sidebar',
                $sidebar_plugin_url,
                ['wp-edit-post'],
                filemtime($sidebar_plugin_path)
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

        if ($screen->base === 'artpulse_page_artpulse-engagement') {
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

        if ($screen->base === 'artpulse_page_artpulse-quickstart') {
            $qs_path = $plugin_dir . '/assets/js/ap-quickstart.js';
            $qs_url  = $plugin_url . '/assets/js/ap-quickstart.js';
            if (file_exists($qs_path)) {
                wp_enqueue_script(
                    'ap-quickstart',
                    $qs_url,
                    [],
                    filemtime($qs_path),
                    true
                );
                wp_localize_script('ap-quickstart', 'apQuickstart', [
                    'mermaidUrl' => plugins_url('assets/libs/mermaid/mermaid.min.js', ARTPULSE_PLUGIN_FILE),
                ]);
            }
        }

        if ($screen->base === 'artpulse_page_ap-dashboard-widgets') {
            $script_path = $plugin_dir . '/assets/dist/admin-dashboard-widgets-editor.js';
            $script_url  = $plugin_url . '/assets/dist/admin-dashboard-widgets-editor.js';
            $style_path  = $plugin_dir . '/assets/css/dashboard-widget.css';
            $style_url   = $plugin_url . '/assets/css/dashboard-widget.css';
            if (file_exists($script_path)) {
                wp_enqueue_script(
                    'sortablejs',
                    plugins_url('assets/libs/sortablejs/Sortable.min.js', ARTPULSE_PLUGIN_FILE),
                    [],
                    null,
                    true
                );
                wp_enqueue_script(
                    'ap-dashboard-widgets-editor',
                    $script_url,
                    ['wp-element', 'wp-data', 'sortablejs'],
                    filemtime($script_path),
                    true
                );
                if (file_exists($style_path)) {
                    wp_enqueue_style(
                        'ap-dashboard-widget',
                        $style_url,
                        [],
                        filemtime($style_path)
                    );
                }
                $config = get_option('ap_dashboard_widget_config', false);
                if (false === $config) {
                    $definitions = \ArtPulse\Core\DashboardWidgetRegistry::get_definitions();
                    $all_ids     = array_column($definitions, 'id');
                    $config      = [];
                    foreach (wp_roles()->roles as $role_key => $role_data) {
                        $config[$role_key] = $all_ids;
                    }
                    update_option('ap_dashboard_widget_config', $config);
                }
                wp_localize_script('ap-dashboard-widgets-editor', 'APDashboardWidgetsEditor', [
                    'widgets' => \ArtPulse\Core\DashboardWidgetRegistry::get_definitions(true),
                    'config'  => $config,
                    'roles'   => wp_roles()->roles,
                    'nonce'   => wp_create_nonce('ap_dashboard_widget_config'),
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'l10n'    => [
                        'availableWidgets' => __('Available Widgets', 'artpulse'),
                        'activeWidgets'    => __('Active Widgets', 'artpulse'),
                        'selectRole'      => __('Select Role', 'artpulse'),
                        'save'            => __('Save', 'artpulse'),
                        'preview'         => __('Preview', 'artpulse'),
                        'resetDefault'    => __('Reset to Default', 'artpulse'),
                        'saveSuccess'     => __('Widget order saved.', 'artpulse'),
                        'saveError'       => __('Error saving widget order.', 'artpulse'),
                        'instructions'    => __('Drag widgets to add, remove, or reorder. Changes are saved for each role.', 'artpulse'),
                    ],
                ]);
            }
        }

        if (
            ($screen->base === 'artpulse_page_artpulse-settings' && ($_GET['tab'] ?? '') === 'import_export') ||
            $screen->base === 'artpulse_page_artpulse-import-export'
        ) {
            wp_enqueue_script(
                'papaparse',
                plugins_url('assets/libs/papaparse/papaparse.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
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
                ['wp-api-fetch', 'chart-js'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-user-dashboard-js', 'ArtPulseDashboardApi', [
                'root'             => esc_url_raw(rest_url()),
                'nonce'            => wp_create_nonce('wp_rest'),
                'orgSubmissionUrl' => self::get_org_submission_url(),
                'artistSubmissionUrl' => self::get_artist_submission_url(),
                'artistEndpoint'   => esc_url_raw(rest_url('artpulse/v1/artist-upgrade')),
                'exportEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/export')),
                'deleteEndpoint'   => esc_url_raw(rest_url('artpulse/v1/user/delete')),
            ]);
            wp_enqueue_script(
                'ap-dashboard',
                $plugin_url . '/assets/js/ap-dashboard.js',
                ['wp-element'],
                '1.0.0',
                true
            );
            $user = wp_get_current_user();
            $role = $user->roles[0] ?? '';
            wp_localize_script('ap-dashboard', 'APDashboard', [
                'role' => $role,
            ]);
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
            wp_localize_script(
                'ap-my-follows-js',
                'ArtPulseFollowsApi',
                [
                    'root'     => esc_url_raw(rest_url()),
                    'nonce'    => wp_create_nonce('wp_rest'),
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'user_id'  => get_current_user_id(),
                ]
            );
         }
    }

    public static function enqueue_frontend() {
        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);

        $enqueue_rest_lists = false;
        if (!is_admin()) {
            global $post;
            if ($post instanceof \WP_Post) {
                if (has_shortcode($post->post_content, 'ap_recommendations') || has_shortcode($post->post_content, 'ap_collection')) {
                    $enqueue_rest_lists = true;
                }
            }
        }

        if (is_singular('artpulse_event')) {
            $swiper_css_path = $plugin_dir . '/assets/libs/swiper/swiper-bundle.min.css';
            wp_enqueue_style(
                'swiper-css',
                plugins_url('assets/libs/swiper/swiper-bundle.min.css', ARTPULSE_PLUGIN_FILE),
                [],
                file_exists($swiper_css_path) ? filemtime($swiper_css_path) : null
            );
            wp_enqueue_script(
                'swiper-js',
                plugins_url('assets/libs/swiper/swiper-bundle.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
            wp_enqueue_script(
                'ap-event-gallery-front',
                plugins_url('assets/js/ap-event-gallery-front.js', ARTPULSE_PLUGIN_FILE),
                ['swiper-js'],
                null,
                true
            );
        }

        if (is_singular('artpulse_org')) {
            $swiper_css_path = $plugin_dir . '/assets/libs/swiper/swiper-bundle.min.css';
            wp_enqueue_style(
                'swiper-css',
                plugins_url('assets/libs/swiper/swiper-bundle.min.css', ARTPULSE_PLUGIN_FILE),
                [],
                file_exists($swiper_css_path) ? filemtime($swiper_css_path) : null
            );
            wp_enqueue_script(
                'swiper-js',
                plugins_url('assets/libs/swiper/swiper-bundle.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
            wp_enqueue_script(
                'ap-event-gallery-front',
                plugins_url('assets/js/ap-event-gallery-front.js', ARTPULSE_PLUGIN_FILE),
                ['swiper-js'],
                null,
                true
            );
        }

        if (is_singular('portfolio')) {
            $swiper_css_path = $plugin_dir . '/assets/libs/swiper/swiper-bundle.min.css';
            wp_enqueue_style(
                'swiper-css',
                plugins_url('assets/libs/swiper/swiper-bundle.min.css', ARTPULSE_PLUGIN_FILE),
                [],
                file_exists($swiper_css_path) ? filemtime($swiper_css_path) : null
            );
            wp_enqueue_script(
                'swiper-js',
                plugins_url('assets/libs/swiper/swiper-bundle.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
            wp_enqueue_script(
                'ap-portfolio-gallery-front',
                plugins_url('assets/js/ap-portfolio-gallery-front.js', ARTPULSE_PLUGIN_FILE),
                ['swiper-js'],
                null,
                true
            );
        }


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
            'chart-js',
            plugins_url('assets/libs/chart.js/chart.min.js', ARTPULSE_PLUGIN_FILE),
            [],
            null,
            true
        );
        wp_enqueue_script(
            'ap-favorite-toggle',
            $plugin_url . '/assets/js/ap-favorite-toggle.js',
            [],
            '1.0.0',
            true
        );
        wp_localize_script('ap-favorite-toggle', 'APFavorites', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'ap-notifications-js',
            $plugin_url . '/assets/js/ap-notifications.js',
            ['wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'ap-messages',
            $plugin_url . '/assets/js/ap-messages.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_enqueue_script(
            'ap-forum-js',
            $plugin_url . '/assets/js/ap-forum.js',
            ['wp-element', 'wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-notifications-js', 'APNotifications', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
        wp_localize_script('ap-notifications-js', 'APNotifyData', [
            'rest_url' => esc_url_raw(rest_url()),
            'nonce'    => wp_create_nonce('wp_rest'),
        ]);
        wp_localize_script('ap-messages', 'APMessages', [
            'restUrl' => esc_url_raw(rest_url('artpulse/v1/conversations')),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
        wp_localize_script('ap-forum-js', 'APForum', [
            'rest_url'    => esc_url_raw(rest_url()),
            'nonce'       => wp_create_nonce('wp_rest'),
            'can_comment' => is_user_logged_in(),
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
            'dashboardUrl'  => \ArtPulse\Core\Plugin::get_user_dashboard_url(),
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
            'dashboardUrl'  => \ArtPulse\Core\Plugin::get_user_dashboard_url(),
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

        wp_enqueue_style(
            'ap-event-filter-form',
            $plugin_url . '/assets/css/ap-event-filter-form.css',
            [],
            '1.0.0'
        );
        wp_enqueue_script(
            'ap-event-filter',
            $plugin_url . '/assets/js/ap-event-filter.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-event-filter', 'APEventFilter', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        wp_enqueue_script(
            'ap-my-follows',
            $plugin_url . '/assets/js/ap-my-follows.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_localize_script(
            'ap-my-follows',
            'ArtPulseFollowsApi',
            [
                'root'     => esc_url_raw(rest_url()),
                'nonce'    => wp_create_nonce('wp_rest'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'user_id'  => get_current_user_id(),
            ]
        );

        $org_dashboard_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-org-dashboard.js';
        $org_dashboard_url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-org-dashboard.js';
        if (file_exists($org_dashboard_path)) {
            wp_enqueue_script(
                'ap-org-dashboard',
                $org_dashboard_url,
                ['jquery'],
                '1.0.0',
                true
            );

            $stage_groups = [];
            $org_id = 0;
            if (is_user_logged_in()) {
                $uid    = get_current_user_id();
                $org_id = get_user_meta($uid, 'ap_organization_id', true);
                if ($org_id) {
                    $stage_groups = OrganizationDashboardShortcode::get_project_stage_groups($org_id);
                }
            }

            wp_localize_script('ap-org-dashboard', 'APOrgDashboard', [
                'ajax_url'     => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('ap_org_dashboard_nonce'),
                'eventFormUrl' => Plugin::get_event_submission_url(),
                'rest_root'    => esc_url_raw(rest_url()),
                'rest_nonce'   => wp_create_nonce('wp_rest'),
                'projectStages'=> $stage_groups,
            ]);
            wp_localize_script('ap-org-dashboard', 'APDashboardData', [
                'rest_url' => esc_url_raw(rest_url()),
                'nonce'    => wp_create_nonce('wp_rest'),
            ]);

            wp_localize_script('ap-org-dashboard', 'APOrgWebhooks', [
                'apiRoot' => esc_url_raw(rest_url()),
                'nonce'   => wp_create_nonce('wp_rest'),
                'orgId'   => absint($org_id),
            ]);

            $wizard_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/rsvp-wizard.js';
            $wizard_url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/rsvp-wizard.js';
            if (file_exists($wizard_path)) {
                wp_enqueue_script(
                    'ap-rsvp-wizard',
                    $wizard_url,
                    ['ap-org-dashboard'],
                    '1.0.0',
                    true
                );
                wp_localize_script('ap-rsvp-wizard', 'APRsvpWizard', [
                    'ajax_url'   => admin_url('admin-ajax.php'),
                    'nonce'      => wp_create_nonce('ap_org_dashboard_nonce'),
                    'rest_root'  => esc_url_raw(rest_url()),
                    'rest_nonce' => wp_create_nonce('wp_rest'),
                ]);
            }
        }

        $artist_dashboard_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-artist-dashboard.js';
        $artist_dashboard_url  = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . '/assets/js/ap-artist-dashboard.js';
        if (file_exists($artist_dashboard_path)) {
            wp_enqueue_script(
                'chart-js',
                plugins_url('assets/libs/chart.js/chart.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
            // Ensure the SortableJS library is available for drag and drop
            // interactions on the artist dashboard.
            wp_enqueue_script(
                'sortablejs',
                plugins_url('assets/libs/sortablejs/Sortable.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                null,
                true
            );
            wp_enqueue_script(
                'ap-artist-dashboard',
                $artist_dashboard_url,
                ['jquery', 'chart-js', 'sortablejs'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-artist-dashboard', 'APArtistDashboard', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ap_artist_dashboard_nonce'),
            ]);
        }

        wp_enqueue_script(
            'ap-dashboard',
            $plugin_url . '/assets/js/ap-dashboard.js',
            ['wp-element'],
            '1.0.0',
            true
        );
        $user = wp_get_current_user();
        $role = $user->roles[0] ?? '';
        wp_localize_script('ap-dashboard', 'APDashboard', [
            'role' => $role,
        ]);


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

        if ($enqueue_rest_lists) {
            wp_enqueue_script(
                'ap-rest-lists',
                $plugin_url . '/assets/js/ap-rest-lists.js',
                ['wp-api-fetch'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-rest-lists', 'APRestLists', [
                'root'  => esc_url_raw(rest_url()),
                'nonce'        => wp_create_nonce('wp_rest'),
                'noItemsText'  => __('No items found.', 'artpulse'),
            ]);
        }

        if (is_singular('artpulse_artist') || is_singular('artpulse_event')) {
            wp_enqueue_script(
                'ap-newsletter-optin',
                $plugin_url . '/assets/js/newsletter-optin.js',
                [],
                '1.0.0',
                true
            );
            wp_localize_script('ap-newsletter-optin', 'APNewsletter', [
                'endpoint'    => esc_url_raw(rest_url('artpulse/v1/newsletter-optin')),
                'nonce'       => wp_create_nonce('wp_rest'),
                'successText' => __('Subscribed!', 'artpulse'),
                'errorText'   => __('Subscription failed.', 'artpulse'),
            ]);
        }

        if (is_singular('artpulse_artist')) {
            wp_enqueue_script(
                'ap-bio-summary',
                $plugin_url . '/assets/js/bio-summary.js',
                ['wp-api-fetch'],
                '1.0.0',
                true
            );
            wp_localize_script('ap-bio-summary', 'APBioSummary', [
                'root'  => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    private static function get_org_submission_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_organization]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    private static function get_artist_submission_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_artist]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }
}