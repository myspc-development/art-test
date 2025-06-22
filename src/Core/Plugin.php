<?php

namespace ArtPulse\Core;

class Plugin
{
    private const VERSION = '1.1.5';

    public function __construct()
    {
        $this->define_constants();
        $this->register_hooks();
    }

    private function define_constants()
    {
        if (!defined('ARTPULSE_VERSION')) {
            define('ARTPULSE_VERSION', self::VERSION);
        }
        if (!defined('ARTPULSE_PLUGIN_DIR')) {
            define('ARTPULSE_PLUGIN_DIR', plugin_dir_path(dirname(dirname(__FILE__))));
        }
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', ARTPULSE_PLUGIN_DIR . 'artpulse-management.php');
        }
    }

    private function register_hooks()
    {
        register_activation_hook(ARTPULSE_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(ARTPULSE_PLUGIN_FILE, [$this, 'deactivate']);

        add_action('init', [$this, 'register_core_modules']);
        add_action('init', [\ArtPulse\Frontend\SubmissionForms::class, 'register']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        add_action('rest_api_init', [\ArtPulse\Community\NotificationRestController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Rest\SubmissionRestController::class, 'register']);
        add_action('init', [$this, 'maybe_migrate_org_meta']);
    }

    public function activate()
    {
        $db_version_option = 'artpulse_db_version';

        // Init settings
        $settings = get_option('artpulse_settings', []);
        $settings['version'] = self::VERSION;
        update_option('artpulse_settings', $settings);

        // DB setup
        if (get_option($db_version_option) !== self::VERSION) {
            \ArtPulse\Community\FavoritesManager::install_favorites_table();
            \ArtPulse\Community\ProfileLinkRequestManager::install_link_request_table();
            \ArtPulse\Community\FollowManager::install_follows_table();
            \ArtPulse\Community\NotificationManager::install_notifications_table();
            update_option($db_version_option, self::VERSION);
        }

        // Register CPTs and flush rewrite rules
        \ArtPulse\Core\PostTypeRegistrar::register();
        flush_rewrite_rules();

        // ✅ Fix: ensure roles/caps are installed
        require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
        \ArtPulse\Core\RoleSetup::install();

        // Schedule cron
        if (!wp_next_scheduled('ap_daily_expiry_check')) {
            wp_schedule_event(time(), 'daily', 'ap_daily_expiry_check');
        }
    }

    public function deactivate()
    {
        flush_rewrite_rules();
        wp_clear_scheduled_hook('ap_daily_expiry_check');
    }

    public function register_core_modules()
    {
        \ArtPulse\Core\PostTypeRegistrar::register(); // ✅ CPTs
        \ArtPulse\Core\MetaBoxRegistrar::register();
        \ArtPulse\Core\AdminDashboard::register();
        \ArtPulse\Core\ShortcodeManager::register();
        \ArtPulse\Admin\SettingsPage::register();
        \ArtPulse\Core\MembershipManager::register();
        \ArtPulse\Core\AccessControlManager::register();
        \ArtPulse\Core\DirectoryManager::register();
        \ArtPulse\Core\UserDashboardManager::register();
        \ArtPulse\Core\AnalyticsManager::register();
        \ArtPulse\Core\AnalyticsDashboard::register();
        \ArtPulse\Core\FrontendMembershipPage::register();
        \ArtPulse\Community\ProfileLinkRequestManager::register();
        \ArtPulse\Core\MyFollowsShortcode::register();
        \ArtPulse\Core\NotificationShortcode::register();
        \ArtPulse\Admin\AdminListSorting::register();
        \ArtPulse\Rest\RestSortingSupport::register();
        \ArtPulse\Admin\AdminListColumns::register();
        \ArtPulse\Admin\EnqueueAssets::register();
        \ArtPulse\Frontend\Shortcodes::register();
        \ArtPulse\Frontend\MyEventsShortcode::register();
        \ArtPulse\Frontend\EventSubmissionShortcode::register();
        \ArtPulse\Frontend\EditEventShortcode::register();
        \ArtPulse\Frontend\OrganizationDashboardShortcode::register();
        \ArtPulse\Frontend\OrganizationEventForm::register();
        \ArtPulse\Frontend\OrganizationSubmissionForm::register();
        \ArtPulse\Frontend\UserProfileShortcode::register();
        \ArtPulse\Frontend\ProfileEditShortcode::register();
        \ArtPulse\Frontend\PortfolioBuilder::register();
        \ArtPulse\Admin\MetaBoxesRelationship::register();
        \ArtPulse\Blocks\RelatedItemsSelectorBlock::register();
        \ArtPulse\Admin\ApprovalManager::register();
        \ArtPulse\Admin\PendingSubmissionsPage::register();
        \ArtPulse\Rest\RestRoutes::register();

        \ArtPulse\Admin\MetaBoxesArtist::register();
        \ArtPulse\Admin\MetaBoxesArtwork::register();
        \ArtPulse\Admin\MetaBoxesEvent::register();
        \ArtPulse\Admin\MetaBoxesOrganisation::register();

        \ArtPulse\Admin\AdminColumnsArtist::register();
        \ArtPulse\Admin\AdminColumnsArtwork::register();
        \ArtPulse\Admin\AdminColumnsEvent::register();
        \ArtPulse\Admin\AdminColumnsOrganisation::register();
        \ArtPulse\Admin\QuickStartGuide::register();
        \ArtPulse\Taxonomies\TaxonomiesRegistrar::register();

        if (class_exists('\\ArtPulse\\Ajax\\FrontendFilterHandler')) {
            \ArtPulse\Ajax\FrontendFilterHandler::register();
        }

        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['woo_enabled'])) {
            \ArtPulse\Core\WooCommerceIntegration::register();
            \ArtPulse\Core\PurchaseShortcode::register();
        }
    }

    public function enqueue_frontend_scripts()
    {
        wp_enqueue_script(
            'ap-membership-account-js',
            plugins_url('assets/js/ap-membership-account.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-favorites-js',
            plugins_url('assets/js/ap-favorites.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-notifications-js',
            plugins_url('assets/js/ap-notifications.js', ARTPULSE_PLUGIN_FILE),
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
            plugins_url('assets/js/ap-submission-form.js', ARTPULSE_PLUGIN_FILE),
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
            plugins_url('assets/js/ap-org-submission.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-org-submission-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_style(
            'ap-forms-css',
            plugins_url('assets/css/ap-forms.css', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0'
        );

        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['service_worker_enabled'])) {
            wp_enqueue_script(
                'ap-sw-loader',
                plugins_url('assets/js/sw-loader.js', ARTPULSE_PLUGIN_FILE),
                [],
                '1.0.0',
                true
            );

            wp_localize_script('ap-sw-loader', 'APServiceWorker', [
                'url'     => plugins_url('assets/js/service-worker.js', ARTPULSE_PLUGIN_FILE),
                'enabled' => true,
            ]);
        }
    }

    public function maybe_migrate_org_meta()
    {
        if (get_option('ap_org_meta_prefix') === 'ead_org') {
            return;
        }

        $posts = get_posts([
            'post_type'      => 'artpulse_org',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => '_ap_org_address', 'compare' => 'EXISTS' ],
                [ 'key' => '_ap_org_website', 'compare' => 'EXISTS' ],
            ],
        ]);

        foreach ($posts as $post_id) {
            $address = get_post_meta($post_id, '_ap_org_address', true);
            if ($address && !get_post_meta($post_id, 'ead_org_street_address', true)) {
                update_post_meta($post_id, 'ead_org_street_address', $address);
            }

            $website = get_post_meta($post_id, '_ap_org_website', true);
            if ($website && !get_post_meta($post_id, 'ead_org_website_url', true)) {
                update_post_meta($post_id, 'ead_org_website_url', $website);
            }
        }

        update_option('ap_org_meta_prefix', 'ead_org');
    }
}
