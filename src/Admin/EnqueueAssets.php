<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\Plugin;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

class EnqueueAssets {

    /** ---------- Small helpers for consistent assets ---------- */
    private static function asset_path(string $rel): string {
        $rel = ltrim($rel, '/');
        return plugin_dir_path(ARTPULSE_PLUGIN_FILE) . $rel;
    }
    private static function asset_url(string $rel): string {
        $rel = ltrim($rel, '/');
        return plugin_dir_url(ARTPULSE_PLUGIN_FILE) . $rel;
    }
    private static function enqueue_style_if_exists(string $handle, string $rel, array $deps = []): void {
        $path = self::asset_path($rel);
        if (file_exists($path)) {
            wp_enqueue_style($handle, self::asset_url($rel), $deps, filemtime($path));
        }
    }
    private static function enqueue_script_if_exists(string $handle, string $rel, array $deps = [], bool $in_footer = true): void {
        $path = self::asset_path($rel);
        if (file_exists($path)) {
            wp_enqueue_script($handle, self::asset_url($rel), $deps, filemtime($path), $in_footer);
        }
    }
    private static function register_chart_js(): void {
        // Make Chart.js available in both admin + frontend
        $rel = 'assets/libs/chart.js/4.4.1/chart.min.js';
        $path = self::asset_path($rel);
        $url  = self::asset_url($rel);
        if (!wp_script_is('chart-js', 'registered') && file_exists($path)) {
            wp_register_script('chart-js', $url, [], filemtime($path), true);
        }
    }

    public static function register() {
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_assets']);
        // FIX: There is no core hook named 'enqueue_block_editor_styles'. Use 'enqueue_block_editor_assets' again.
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_styles']);

        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend']);

        // Dashboard-specific admin pages
        add_action('admin_enqueue_scripts', function ($hook) {
            $dashboard_pages = ['toplevel_page_ap-dashboard', 'toplevel_page_ap-org-dashboard'];
            if (!in_array($hook, $dashboard_pages, true)) {
                return;
            }

            // Core dashboard CSS/JS
            self::enqueue_style_if_exists('ap-quickstart-guides', 'assets/css/ap-quickstart-guides.css');
            self::enqueue_style_if_exists('ap-dashboard',          'assets/css/dashboard.css');

            self::enqueue_script_if_exists('ap-role-tabs',         'assets/js/dashboard-role-tabs.js');
            // Sortable (if shipped)
            if (file_exists(self::asset_path('assets/libs/sortablejs/Sortable.min.js'))) {
                wp_enqueue_script(
                    'sortablejs',
                    self::asset_url('assets/libs/sortablejs/Sortable.min.js'),
                    [],
                    '1.15.0',
                    true
                );
            }
            self::enqueue_script_if_exists('role-dashboard',       'assets/js/role-dashboard.js', ['sortablejs']);
        });
    }

    /** ---------------- Block editor (Gutenberg) ---------------- */
    public static function enqueue_block_editor_assets() {
        if (!defined('ARTPULSE_PLUGIN_FILE')) return;

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || (method_exists($screen, 'is_block_editor') && !$screen->is_block_editor())) return;

        // Event gallery (CPT)
        self::enqueue_script_if_exists('ap-event-gallery', 'assets/js/ap-event-gallery.js', ['jquery', 'jquery-ui-sortable']);
        if (wp_script_is('ap-event-gallery', 'enqueued')) {
            wp_localize_script('ap-event-gallery', 'APEvtGallery', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ap_event_gallery_nonce'),
            ]);
        }

        // Sidebar taxonomy selector
        self::enqueue_script_if_exists(
            'artpulse-taxonomy-sidebar',
            'assets/js/sidebar-taxonomies.js',
            ['wp-edit-post', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins']
        );

        // Admin sidebar plugin (if present)
        self::enqueue_script_if_exists('artpulse-sidebar', 'admin/sidebar.js', ['wp-edit-post']);

        // Advanced taxonomy filter block
        self::enqueue_script_if_exists(
            'artpulse-advanced-taxonomy-filter-block',
            'assets/js/advanced-taxonomy-filter-block.js',
            ['wp-blocks', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins']
        );

        // Filtered list shortcode block
        self::enqueue_script_if_exists(
            'artpulse-filtered-list-shortcode-block',
            'assets/js/filtered-list-shortcode-block.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-compose', 'wp-plugins']
        );

        // AJAX taxonomy filter block
        self::enqueue_script_if_exists(
            'artpulse-ajax-filter-block',
            'assets/js/ajax-filter-block.js',
            ['wp-blocks', 'wp-data', 'wp-components', 'wp-element', 'wp-compose', 'wp-plugins']
        );
    }

    public static function enqueue_block_editor_styles() {
        if (!defined('ARTPULSE_PLUGIN_FILE')) return;

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || (method_exists($screen, 'is_block_editor') && !$screen->is_block_editor())) return;

        self::enqueue_style_if_exists('artpulse-editor-styles', 'assets/css/editor-styles.css');
    }

    /** ------------------------- Admin ------------------------- */
    public static function enqueue_admin() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen) return;

        // Only load admin assets on ArtPulse-related screens
        if (strpos((string) $screen->id, 'artpulse') === false) return;

        self::register_chart_js();

        // Engagement dashboard
        if ($screen->base === 'artpulse-settings_page_artpulse-engagement') {
            self::enqueue_script_if_exists('ap-engagement-dashboard', 'assets/js/ap-engagement-dashboard.js');
        }

        // Quickstart page
        if ($screen->base === 'artpulse-settings_page_artpulse-quickstart') {
            self::enqueue_script_if_exists('ap-quickstart', 'assets/js/ap-quickstart.js');
            if (wp_script_is('ap-quickstart', 'enqueued')) {
                wp_localize_script('ap-quickstart', 'apQuickstart', [
                    'mermaidUrl' => plugins_url('assets/libs/mermaid/mermaid.min.js', ARTPULSE_PLUGIN_FILE),
                ]);
            }
        }

        // Import/export
        $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        if (($screen->base === 'toplevel_page_artpulse-settings' && $tab === 'import_export') ||
            $screen->base === 'artpulse-settings_page_artpulse-import-export'
        ) {
            wp_enqueue_script(
                'papaparse',
                plugins_url('assets/libs/papaparse/papaparse.min.js', ARTPULSE_PLUGIN_FILE),
                [],
                '5.5.3',
                true
            );
            self::enqueue_script_if_exists('ap-csv-import', 'assets/js/ap-csv-import.js', ['papaparse', 'wp-api-fetch']);
            if (wp_script_is('ap-csv-import', 'enqueued')) {
                wp_localize_script('ap-csv-import', 'APCSVImport', [
                    'endpoint'     => esc_url_raw(rest_url('artpulse/v1/import')),
                    'templateBase' => esc_url_raw(rest_url('artpulse/v1/import-template')),
                    'nonce'        => wp_create_nonce('wp_rest'),
                ]);
            }
        }

        // Core admin styles + dashboard script (non-React)
        if (!wp_script_is('ap-user-dashboard-js', 'enqueued')) {
            self::enqueue_style_if_exists('ap-style', 'assets/css/ap-style.css');
            // Ensure Chart.js is registered as dependency
            wp_enqueue_script(
                'ap-user-dashboard-js',
                self::asset_url('assets/js/ap-user-dashboard.js'),
                ['wp-api-fetch', 'chart-js'],
                file_exists(self::asset_path('assets/js/ap-user-dashboard.js')) ? filemtime(self::asset_path('assets/js/ap-user-dashboard.js')) : '1.0.0',
                true
            );

            $opts = get_option('artpulse_settings', []);
            // Avoid exposing secrets unless absolutely necessary.
            $apiToken = ''; // ($opts['external_api_token'] ?? ''); // <- intentionally blanked
            wp_localize_script('ap-user-dashboard-js', 'ArtPulseDashboardApi', [
                'root'               => esc_url_raw(rest_url()),
                'nonce'              => wp_create_nonce('wp_rest'),
                'orgSubmissionUrl'   => self::get_org_submission_url(),
                'artistSubmissionUrl'=> self::get_artist_submission_url(),
                'artistEndpoint'     => esc_url_raw(rest_url('artpulse/v1/artist-upgrade')),
                'exportEndpoint'     => esc_url_raw(rest_url('artpulse/v1/user/export')),
                'deleteEndpoint'     => esc_url_raw(rest_url('artpulse/v1/user/delete')),
                'ajaxUrl'            => admin_url('admin-ajax.php'),
                'apiUrl'             => esc_url_raw($opts['external_api_base_url'] ?? ''),
                'apiToken'           => $apiToken,
            ]);
        }

        // FIX: handle mismatch — standardize to 'ap-analytics' everywhere
        if (!wp_script_is('ap-analytics', 'enqueued')) {
            self::enqueue_script_if_exists('ap-analytics', 'assets/js/ap-analytics.js');
        }

        if (!wp_script_is('ap-my-follows-js', 'enqueued')) {
            self::enqueue_script_if_exists('ap-my-follows-js', 'assets/js/ap-my-follows.js');
            if (wp_script_is('ap-my-follows-js', 'enqueued')) {
                wp_localize_script('ap-my-follows-js', 'ArtPulseFollowsApi', [
                    'root'     => esc_url_raw(rest_url()),
                    'nonce'    => wp_create_nonce('wp_rest'),
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'user_id'  => get_current_user_id(),
                ]);
            }
        }
    }

    /** ------------------------ Frontend ------------------------ */
    public static function enqueue_frontend() {
        self::register_chart_js();

        $enqueue_rest_lists = false;
        if (!is_admin()) {
            global $post;
            if ($post instanceof \WP_Post) {
                if (has_shortcode($post->post_content, 'ap_recommendations') || has_shortcode($post->post_content, 'ap_collection')) {
                    $enqueue_rest_lists = true;
                }
            }
        }

        // Swiper + galleries on various singular pages
        $needs_swiper = is_singular('artpulse_event') || is_singular('artpulse_org') || is_singular('portfolio');
        if ($needs_swiper) {
            // Swiper CSS
            $swiper_css_rel = 'assets/libs/swiper/swiper-bundle.min.css';
            $swiper_css_path = self::asset_path($swiper_css_rel);
            wp_enqueue_style(
                'swiper-css',
                self::asset_url($swiper_css_rel),
                [],
                file_exists($swiper_css_path) ? filemtime($swiper_css_path) : null
            );
            self::enqueue_style_if_exists('ap-swiper', 'assets/css/swiper.css', ['swiper-css']);

            // Swiper JS
            wp_enqueue_script('swiper-js', self::asset_url('assets/libs/swiper/swiper-bundle.min.js'), [], null, true);

            // Page-specific scripts
            if (is_singular('artpulse_event') || is_singular('artpulse_org')) {
                self::enqueue_script_if_exists('ap-event-gallery-front', 'assets/js/ap-event-gallery-front.js', ['swiper-js']);
            }
            if (is_singular('portfolio')) {
                self::enqueue_script_if_exists('ap-portfolio-gallery-front', 'assets/js/ap-portfolio-gallery-front.js', ['swiper-js']);
                self::enqueue_script_if_exists('ap-related-carousel',      'assets/js/ap-related-carousel.js',      ['swiper-js']);
            }
        }

        // Membership/account & favorites/share
        wp_enqueue_script('ap-membership-account-js', self::asset_url('assets/js/ap-membership-account.js'), ['wp-api-fetch'], '1.0.0', true);
        wp_enqueue_script('ap-favorites-js',          self::asset_url('assets/js/ap-favorites.js'),          [],                '1.0.0', true);

        // Chart.js available on frontend (registered above)
        if (!wp_script_is('chart-js', 'enqueued') && wp_script_is('chart-js', 'registered')) {
            wp_enqueue_script('chart-js');
        }

        wp_enqueue_script('ap-favorite-toggle', self::asset_url('assets/js/ap-favorite-toggle.js'), [], '1.0.0', true);
        wp_localize_script('ap-favorite-toggle', 'APFavorites', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script('ap-share', self::asset_url('assets/js/share.js'), [], '1.0.0', true);
        wp_localize_script('ap-share', 'APShare', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script('ap-notifications-js', self::asset_url('assets/js/ap-notifications.js'), ['wp-api-fetch'], '1.0.0', true);
        wp_enqueue_script('ap-messages-js',      self::asset_url('assets/js/ap-messages.js'),      ['wp-api-fetch'], '1.0.0', true);
        wp_enqueue_script('ap-forum-js',         self::asset_url('assets/js/ap-forum.js'),         ['wp-element','wp-api-fetch'], '1.0.0', true);

        wp_localize_script('ap-notifications-js', 'APNotifications', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);
        wp_localize_script('ap-notifications-js', 'APNotifyData', [
            'rest_url' => esc_url_raw(rest_url()),
            'nonce'    => wp_create_nonce('wp_rest'),
        ]);
        wp_localize_script('ap-messages-js', 'APMessages', [
            'apiRoot'  => esc_url_raw(rest_url()),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wp_rest'),
            'pollId'   => 0,
            'loggedIn' => is_user_logged_in(),
        ]);
        wp_localize_script('ap-forum-js', 'APForum', [
            'rest_url'    => esc_url_raw(rest_url()),
            'nonce'       => wp_create_nonce('wp_rest'),
            'can_comment' => is_user_logged_in(),
        ]);

        // Submissions
        wp_enqueue_script('ap-submission-form-js', self::asset_url('assets/js/ap-submission-form.js'), ['wp-api-fetch'], '1.0.0', true);
        wp_localize_script('ap-submission-form-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
            'dashboardUrl'  => Plugin::get_user_dashboard_url(),
        ]);

        wp_enqueue_script('ap-org-submission-js', self::asset_url('assets/js/ap-org-submission.js'), ['wp-api-fetch'], '1.0.0', true);
        wp_localize_script('ap-org-submission-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
            'dashboardUrl'  => Plugin::get_user_dashboard_url(),
        ]);

        // Location helpers
        wp_enqueue_script('ap-address-autocomplete', self::asset_url('assets/js/address-autocomplete.js'), ['wp-api-fetch'], '1.0.0', true);
        wp_localize_script('ap-address-autocomplete', 'APLocation', [
            'countriesUrl'     => self::asset_url('data/countries.json'),
            'statesUrl'        => self::asset_url('data/states.json'),
            'citiesUrl'        => self::asset_url('data/cities.json'),
            'geonamesEndpoint' => esc_url_raw(rest_url('artpulse/v1/location/geonames')),
            'googleEndpoint'   => esc_url_raw(rest_url('artpulse/v1/location/google')),
        ]);

        wp_enqueue_script('ap-google-places', self::asset_url('assets/js/google-places-autocomplete.js'), ['wp-api-fetch'], '1.0.0', true);

        // Core styles (skip if disabled)
        if (!function_exists('ap_styles_disabled') || !ap_styles_disabled()) {
            self::enqueue_style_if_exists('ap-style', 'assets/css/ap-style.css');
        }

        // Front-end extras
        wp_enqueue_script('ap-analytics', self::asset_url('assets/js/ap-analytics.js'), ['jquery'], '1.0.0', true);
        wp_enqueue_script('ap-directory', self::asset_url('assets/js/ap-directory.js'), ['jquery'], '1.0.0', true);

        self::enqueue_style_if_exists('ap-event-filter-form', 'assets/css/ap-event-filter-form.css');
        wp_enqueue_script('ap-event-filter', self::asset_url('assets/js/ap-event-filter.js'), ['jquery'], '1.0.0', true);
        wp_localize_script('ap-event-filter', 'APEventFilter', ['ajaxurl' => admin_url('admin-ajax.php')]);

        // Follows (frontend)
        wp_enqueue_script('ap-my-follows-js', self::asset_url('assets/js/ap-my-follows.js'), ['jquery'], '1.0.0', true);
        wp_localize_script('ap-my-follows-js', 'ArtPulseFollowsApi', [
            'root'     => esc_url_raw(rest_url()),
            'nonce'    => wp_create_nonce('wp_rest'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_id'  => get_current_user_id(),
        ]);

        // Org dashboard (frontend)
        if (file_exists(self::asset_path('assets/js/ap-org-dashboard.js'))) {
            wp_enqueue_script('ap-org-dashboard', self::asset_url('assets/js/ap-org-dashboard.js'), ['jquery'], '1.0.0', true);

            $stage_groups = [];
            $org_id = 0;
            if (is_user_logged_in()) {
                $uid    = get_current_user_id();
                $org_id = (int) get_user_meta($uid, 'ap_organization_id', true);
                if ($org_id) {
                    $stage_groups = OrganizationDashboardShortcode::get_project_stage_groups($org_id);
                }
            }

            wp_localize_script('ap-org-dashboard', 'APOrgDashboard', [
                'ajax_url'      => admin_url('admin-ajax.php'),
                'nonce'         => wp_create_nonce('ap_org_dashboard_nonce'),
                'eventFormUrl'  => Plugin::get_event_submission_url(),
                'rest_root'     => esc_url_raw(rest_url()),
                'rest_nonce'    => wp_create_nonce('wp_rest'),
                'projectStages' => $stage_groups,
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

            // RSVP wizard (optional)
            if (file_exists(self::asset_path('assets/js/rsvp-wizard.js'))) {
                wp_enqueue_script('ap-rsvp-wizard', self::asset_url('assets/js/rsvp-wizard.js'), ['ap-org-dashboard'], '1.0.0', true);
                wp_localize_script('ap-rsvp-wizard', 'APRsvpWizard', [
                    'ajax_url'   => admin_url('admin-ajax.php'),
                    'nonce'      => wp_create_nonce('ap_org_dashboard_nonce'),
                    'rest_root'  => esc_url_raw(rest_url()),
                    'rest_nonce' => wp_create_nonce('wp_rest'),
                ]);
            }
        }

        // Service worker loader (optional)
        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['service_worker_enabled'])) {
            wp_enqueue_script('ap-sw-loader', self::asset_url('assets/js/sw-loader.js'), [], '1.0.0', true);
            wp_localize_script('ap-sw-loader', 'APServiceWorker', [
                'url'     => self::asset_url('assets/js/service-worker.js'),
                'enabled' => true,
            ]);
        }

        // REST lists (conditional)
        if ($enqueue_rest_lists) {
            wp_enqueue_script('ap-rest-lists', self::asset_url('assets/js/ap-rest-lists.js'), ['wp-api-fetch'], '1.0.0', true);
            wp_localize_script('ap-rest-lists', 'APRestLists', [
                'root'       => esc_url_raw(rest_url()),
                'nonce'      => wp_create_nonce('wp_rest'),
                'noItemsText'=> __('No items found.', 'artpulse'),
            ]);
        }

        // Opt-ins
        if (is_singular('artpulse_artist') || is_singular('artpulse_event')) {
            wp_enqueue_script('ap-newsletter-optin', self::asset_url('assets/js/newsletter-optin.js'), [], '1.0.0', true);
            wp_localize_script('ap-newsletter-optin', 'APNewsletter', [
                'endpoint'    => esc_url_raw(rest_url('artpulse/v1/newsletter-optin')),
                'nonce'       => wp_create_nonce('wp_rest'),
                'successText' => __('Subscribed!', 'artpulse'),
                'errorText'   => __('Subscription failed.', 'artpulse'),
            ]);
        }

        if (is_singular('artpulse_artist')) {
            wp_enqueue_script('ap-bio-summary', self::asset_url('assets/js/bio-summary.js'), ['wp-api-fetch'], '1.0.0', true);
            wp_localize_script('ap-bio-summary', 'APBioSummary', [
                'root'  => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    /** ------------------------- Helpers ------------------------ */
    private static function get_org_submission_url(): string {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_organization]',
            'numberposts' => 1,
        ]);
        return !empty($pages) ? get_permalink($pages[0]->ID) : home_url('/');
    }

    private static function get_artist_submission_url(): string {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_artist]',
            'numberposts' => 1,
        ]);
        return !empty($pages) ? get_permalink($pages[0]->ID) : home_url('/');
    }
}
