<?php

namespace ArtPulse\Core;

use ArtPulse\Core\PortfolioManager;
use ArtPulse\Core\AdminDashboard;
use ArtPulse\Core\DocumentationManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Rest\ArtistRestController;
use ArtPulse\Rest\CurrentUserController;
use ArtPulse\Rest\DashboardMessagesController;
use ArtPulse\Rest\RestRelationships;
use ArtPulse\Rest\TaxonomyRestFilters;
use ArtPulse\Admin\EngagementDashboard;
use ArtPulse\Core\ArtworkEventLinkManager;
use ArtPulse\Engagement\DigestMailer;
use ArtPulse\Frontend\WidgetEmbedShortcode;
use ArtPulse\Blocks\WidgetEmbedBlock;
use ArtPulse\Frontend\ReactDashboardShortcode;

class Plugin
{
    private const VERSION = '1.3.11';

    public static function register(): void
    {
        new self();
    }

    public function __construct()
    {
        $this->define_constants();
        add_action('init', [$this, 'load_textdomain']);
        $this->register_hooks();
        add_action('plugins_loaded', [$this, 'check_plugin_version']);
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
        add_action('rest_api_init', [\ArtPulse\Community\FavoritesRestController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Community\EventCommentsController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Community\EventChatController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Community\ForumRestController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Community\LeaderboardRestController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Community\UnifiedInboxController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Rest\SubmissionRestController::class, 'register']);
        add_action('rest_api_init', [\ArtPulse\Rest\CompetitionRestController::class, 'register']);
        add_action('init', [$this, 'maybe_migrate_org_meta']);
        add_action('init', [$this, 'maybe_migrate_profile_link_request_slug']);
        add_action('init', [$this, 'maybe_add_upload_cap']);
        add_action('init', [$this, 'maybe_add_collection_cap']);
        add_action('init', [\ArtPulse\Core\RoleSetup::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\FavoritesManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\FollowManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\ProfileLinkRequestManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\NotificationManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\DirectMessages::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Admin\LoginEventsPage::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\UserEngagementLogger::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\ProfileMetrics::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\EventMetrics::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\ArtworkEventLinkManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Personalization\RecommendationEngine::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\ActivityLogger::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\DelegatedAccessManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Community\EventChatController::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Core\CompetitionEntryManager::class, 'maybe_install_table']);
        add_action('init', [\ArtPulse\Frontend\CompetitionDashboardShortcode::class, 'register']);
        add_filter('script_loader_tag', [self::class, 'add_defer'], 10, 3);
    }

    public function activate()
    {
        $db_version_option = 'artpulse_db_version';

        // Track installed plugin version
        update_option('ap_plugin_version', self::VERSION);

        // Init settings with defaults
        $defaults = function_exists('artpulse_get_default_settings') ? artpulse_get_default_settings() : [];
        $settings = get_option('artpulse_settings', []);
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        $settings['plugin_version'] = self::VERSION;
        update_option('artpulse_settings', $settings);

        // DB setup
        if (get_option($db_version_option) !== self::VERSION) {
            \ArtPulse\Community\FavoritesManager::install_favorites_table();
            \ArtPulse\Community\ProfileLinkRequestManager::install_link_request_table();
            \ArtPulse\Community\FollowManager::install_follows_table();
            \ArtPulse\Community\NotificationManager::install_notifications_table();
            \ArtPulse\Admin\LoginEventsPage::install_login_events_table();
            \ArtPulse\Core\UserEngagementLogger::install_table();
            \ArtPulse\Core\ProfileMetrics::install_table();
            \ArtPulse\Core\EventMetrics::install_table();
            \ArtPulse\Core\ArtworkEventLinkManager::install_table();
            \ArtPulse\Personalization\RecommendationEngine::install_table();
            \ArtPulse\Core\ActivityLogger::install_table();
            \ArtPulse\Core\FeedbackManager::install_table();
            \ArtPulse\Core\DelegatedAccessManager::install_table();
            \ArtPulse\Core\CompetitionEntryManager::install_table();
            update_option($db_version_option, self::VERSION);
        }

        // Register CPTs and flush rewrite rules
        \ArtPulse\Core\PostTypeRegistrar::register();
        \ArtPulse\Integration\CalendarExport::add_rewrite_rules();
        flush_rewrite_rules();

        // ✅ Fix: ensure roles/caps are installed
        require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
        \ArtPulse\Core\RoleSetup::install();
        $role = get_role('artist');
        $role?->add_cap('ap_send_messages');

        // Schedule cron
        if (!wp_next_scheduled('ap_daily_expiry_check')) {
            wp_schedule_event(time(), 'daily', 'ap_daily_expiry_check');
        }

        \ArtPulse\Engagement\DigestMailer::schedule_cron();
    }

    public function deactivate()
    {
        flush_rewrite_rules();
        wp_clear_scheduled_hook('ap_daily_expiry_check');
        wp_clear_scheduled_hook('ap_daily_digest');
        wp_clear_scheduled_hook('ap_process_scheduled_messages');
    }

    public function register_core_modules()
    {
        \ArtPulse\Core\PostTypeRegistrar::register(); // ✅ CPTs
        \ArtPulse\Core\MetaBoxRegistrar::register();
        \ArtPulse\Core\ShortcodeManager::register();
        \ArtPulse\Admin\SettingsPage::register();
        \ArtPulse\Admin\ShortcodePages::register();
        \ArtPulse\Core\MembershipManager::register();
        \ArtPulse\Core\AccessControlManager::register();
        \ArtPulse\Core\CapabilitiesManager::register();
        DashboardWidgetRegistry::init();
        \ArtPulse\Core\AdminAccessManager::register();
        \ArtPulse\Core\LoginRedirectManager::register();
        \ArtPulse\Core\DashboardAccessManager::register();
        \ArtPulse\Core\DirectoryManager::register();
        \ArtPulse\Core\UserDashboardManager::register();
        \ArtPulse\Core\OrgDashboardManager::register();
        \ArtPulse\Core\AnalyticsManager::register();
        \ArtPulse\Core\AnalyticsDashboard::register();
        \ArtPulse\Admin\PaymentAnalyticsDashboard::register();
        \ArtPulse\Admin\PaymentReportsPage::register();
        EngagementDashboard::register();
        AdminDashboard::register();
        \ArtPulse\Core\FrontendMembershipPage::register();
        \ArtPulse\Community\ProfileLinkRequestManager::register();
        \ArtPulse\Community\ArtistUpgradeRestController::register();
        \ArtPulse\Core\MyFollowsShortcode::register();
        \ArtPulse\Core\NotificationShortcode::register();
        \ArtPulse\Community\UserPreferencesRestController::register();
        \ArtPulse\Rest\WidgetSettingsRestController::register();
        \ArtPulse\Rest\LayoutSaveEndpoint::register();
        \ArtPulse\Core\ProfileMetrics::register();
        \ArtPulse\Core\RoleAuditLogger::register();
        \ArtPulse\Core\ActivityLogger::register();
        \ArtPulse\Core\DelegatedAccessManager::register();
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
        \ArtPulse\Frontend\SubmitArtistForm::register();
        \ArtPulse\Frontend\ArtistDashboardShortcode::register();
        \ArtPulse\Frontend\UserProfileShortcode::register();
        \ArtPulse\Frontend\ProfileEditShortcode::register();
        \ArtPulse\Frontend\ArtistProfileFormShortcode::register();
        \ArtPulse\Frontend\OrgProfileEditShortcode::register();
        \ArtPulse\Frontend\OrgPublicProfileShortcode::register();
        \ArtPulse\Frontend\PayoutsPage::register();
        \ArtPulse\Frontend\AccountSettingsPage::register();
        \ArtPulse\Frontend\PortfolioBuilder::register();
        PortfolioManager::register();
        \ArtPulse\Integration\PortfolioSync::register();
        \ArtPulse\Integration\OAuthManager::register();
        \ArtPulse\Integration\CalendarExport::register();
        \ArtPulse\Integration\SocialAutoPoster::register();
        \ArtPulse\AI\AutoTagger::register();
        \ArtPulse\AI\BioSummaryRestController::register();
        \ArtPulse\Frontend\LoginShortcode::register();
        \ArtPulse\Frontend\RegistrationShortcode::register();
        \ArtPulse\Frontend\LogoutShortcode::register();
        \ArtPulse\Frontend\EventsSliderShortcode::register();
        \ArtPulse\Frontend\EventListingShortcode::register();
        \ArtPulse\Frontend\EventCalendarShortcode::register();
        \ArtPulse\Frontend\EventMapShortcode::register();
        \ArtPulse\Frontend\EventCommentsShortcode::register();
        \ArtPulse\Frontend\EventChatShortcode::register();
        \ArtPulse\Frontend\MessagesShortcode::register();
        \ArtPulse\Frontend\ReactDashboardShortcode::register();
        \ArtPulse\Frontend\RestListShortcodes::register();
        \ArtPulse\Frontend\CollectionsShortcode::register();
        \ArtPulse\Frontend\EventFilter::register();
        \ArtPulse\Frontend\OrgRsvpDashboard::register();
        \ArtPulse\Frontend\EventRsvpHandler::register();
        \ArtPulse\Admin\MetaBoxesRelationship::register();
        \ArtPulse\Blocks\RelatedItemsSelectorBlock::register();
        \ArtPulse\Admin\ApprovalManager::register();
        \ArtPulse\Admin\PendingSubmissionsPage::register();
        \ArtPulse\Admin\SpotlightManager::register();
        \ArtPulse\Admin\SpotlightPostType::register();
        \ArtPulse\Admin\CustomDashboardWidgetPostType::register();
        \ArtPulse\Admin\LoginEventsPage::register();
        \ArtPulse\Admin\OrgUserManager::register();
        \ArtPulse\Admin\OrgRolesPage::register();
        \ArtPulse\Admin\OrgCommunicationsCenter::register();
        \ArtPulse\Admin\ScheduledMessageManager::register();
        \ArtPulse\Admin\PostStatusRejected::register();
        \ArtPulse\Rest\RestRoutes::register();
        \ArtPulse\Rest\LocationRestController::register();
        \ArtPulse\Rest\OrgAnalyticsController::register();
        \ArtPulse\Rest\ProfileMetricsController::register();
        \ArtPulse\Rest\AnalyticsRestController::register();
        \ArtPulse\Rest\ShareController::register();
        \ArtPulse\Rest\PaymentReportsController::register();
        \ArtPulse\Rest\UserAccountRestController::register();
        \ArtPulse\Rest\CollectionRestController::register();
        \ArtPulse\Rest\ArtworkAuctionController::register();
        \ArtPulse\Rest\SpotlightRestController::register();
        \ArtPulse\Rest\SpotlightAnalyticsController::register();
        \ArtPulse\Rest\CurrentUserController::register();
        \ArtPulse\Rest\DashboardMessagesController::register();
        ArtistRestController::register();
        RestRelationships::register();
        TaxonomyRestFilters::register();

        \ArtPulse\Admin\MetaBoxesArtist::register();
        \ArtPulse\Admin\MetaBoxesArtwork::register();
        \ArtPulse\Admin\MetaBoxesEvent::register();
        \ArtPulse\Admin\MetaBoxesCollection::register();
        \ArtPulse\Admin\EventRsvpToggle::register();
        \ArtPulse\Admin\MetaBoxesAddress::register(['artpulse_event']);
        \ArtPulse\Admin\MetaBoxesOrganisation::register();

        \ArtPulse\Admin\AdminColumnsArtist::register();
        \ArtPulse\Admin\AdminColumnsArtwork::register();
        \ArtPulse\Admin\AdminColumnsEvent::register();
        \ArtPulse\Admin\AdminColumnsOrganisation::register();
        \ArtPulse\Admin\QuickStartGuide::register();
        \ArtPulse\Admin\ReleaseNotes::register();
        DocumentationManager::register();
        \ArtPulse\Taxonomies\TaxonomiesRegistrar::register();
        \ArtPulse\Core\EventExpiryCron::register();
        \ArtPulse\Core\EventViewCounter::register();
        \ArtPulse\Core\EventTemplateManager::register();

        if (class_exists('\\ArtPulse\\Ajax\\FrontendFilterHandler')) {
            \ArtPulse\Ajax\FrontendFilterHandler::register();
        }

        $opts = get_option('artpulse_settings', []);
        if (!empty($opts['woocommerce_enabled'])) {
            \ArtPulse\Core\WooCommerceIntegration::register();
            \ArtPulse\Core\PurchaseShortcode::register();
            \ArtPulse\Core\ArtworkWooSync::register();
        }

        \ArtPulse\Admin\AdminColumnsTaxonomies::register();
        \ArtPulse\Admin\MemberEnhancements::register();
        \ArtPulse\Admin\EventNotesTasks::register();
        \ArtPulse\Admin\ProfileLinkRequestAdmin::register();
        add_action('admin_menu', [\ArtPulse\Admin\OrgDashboardAdmin::class, 'register']);
        add_action('admin_menu', [\ArtPulse\Admin\OrgDashboardAdmin::class, 'hide_org_menu'], 999);
        add_action('save_post', [\ArtPulse\Admin\OrgDashboardAdmin::class, 'clear_cache'], 10, 3);
        \ArtPulse\Blocks\AdvancedTaxonomyFilterBlock::register();
        \ArtPulse\Blocks\AjaxFilterBlock::register();
        \ArtPulse\Blocks\FilteredListShortcodeBlock::register();
        \ArtPulse\Blocks\SpotlightBlock::register();
        \ArtPulse\Blocks\FavoritePortfolioBlock::register();
        \ArtPulse\Blocks\TaxonomyFilterBlock::register();
        \ArtPulse\Blocks\PortfolioPreviewBlock::register();
        WidgetEmbedShortcode::register();
        WidgetEmbedBlock::register();
        \ArtPulse\Blocks\FavoritesWidgetBlock::register();
        \ArtPulse\Community\FollowRestController::register();
        \ArtPulse\Community\ProfileLinkRequestRestController::register();
        \ArtPulse\Community\NotificationHooks::register();
        \ArtPulse\Community\DirectMessages::register();
        \ArtPulse\Core\MembershipNotifier::register();
        \ArtPulse\Core\MembershipCron::register();
        \ArtPulse\Engagement\DigestMailer::register();
        \ArtPulse\Core\FeedbackManager::register();
        \ArtPulse\Frontend\FeedbackWidget::register();
        \ArtPulse\Frontend\NewsletterOptinEndpoint::register();
        \ArtPulse\Admin\FeedbackPage::register();
        \ArtPulse\Admin\ReportingManager::register();
        \ArtPulse\Admin\CustomFieldsManager::register();
        \ArtPulse\Admin\SurveyManager::register();
        \ArtPulse\Admin\SegmentationManager::register();
        \ArtPulse\Admin\ReminderManager::register();
        \ArtPulse\Monetization\TicketManager::register();
        \ArtPulse\Monetization\PromoManager::register();
        \ArtPulse\Monetization\MembershipManager::register();
        \ArtPulse\Monetization\PaymentWebhookController::register();
        \ArtPulse\Monetization\SalesOverview::register();
        \ArtPulse\Monetization\PayoutManager::register();
        \ArtPulse\Monetization\TipManager::register();
        \ArtPulse\Integration\WebhookManager::register();
        \ArtPulse\Monetization\EventPromotionManager::register();
        \ArtPulse\Search\MetaFullTextSearch::register();
        \ArtPulse\Search\ExternalSearch::register();
        \ArtPulse\Personalization\RecommendationRestController::register();
        add_action('rest_api_init', [\ArtPulse\Rest\DirectoryController::class, 'register_routes']);
        \ArtPulse\Rest\EventManagementController::register();
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
            'ap-payouts-js',
            plugins_url('assets/js/ap-payouts.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-account-settings-js',
            plugins_url('assets/js/ap-account-settings.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-payouts-js', 'APPayouts', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n'  => [
                'balanceLabel' => __('Current Balance', 'artpulse'),
                'noHistory'    => __('No payouts found.', 'artpulse'),
                'updated'      => __('Settings updated.', 'artpulse'),
            ],
        ]);

        wp_localize_script('ap-account-settings-js', 'APAccountSettings', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n'  => [ 'saved' => __('Settings saved.', 'artpulse') ],
        ]);

        wp_enqueue_script(
            'ap-favorites-js',
            plugins_url('assets/js/ap-favorites.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-follow-feed-js',
            plugins_url('assets/js/favorites.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-follow-feed-js', 'ArtPulseFavoritesFeed', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_script(
            'ap-notifications-js',
            plugins_url('assets/js/ap-notifications.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-messages',
            plugins_url('assets/js/ap-messages.js', ARTPULSE_PLUGIN_FILE),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-event-comments-js',
            plugins_url('assets/js/ap-event-comments.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-comment-js',
            plugins_url('assets/js/comment.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-event-chat-js',
            plugins_url('assets/js/ap-event-chat.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-forum-js',
            plugins_url('assets/js/ap-forum.js', ARTPULSE_PLUGIN_FILE),
            ['wp-element', 'wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'ap-follow-js',
            plugins_url('assets/js/follow.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );

        wp_localize_script('ap-follow-js', 'APFollow', [
            'ajaxurl'      => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('wp_rest'),
            'followText'   => __('Follow', 'artpulse'),
            'unfollowText' => __('Unfollow', 'artpulse'),
        ]);

        wp_enqueue_script(
            'ap-donations-js',
            plugins_url('assets/js/donations.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-donations-js', 'APDonations', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'thanks' => __('Thank you for your support!', 'artpulse'),
        ]);

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

        wp_localize_script('ap-event-comments-js', 'APComments', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_localize_script('ap-comment-js', 'APArtistComments', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
        ]);

        wp_localize_script('ap-event-chat-js', 'APChat', [
            'apiRoot' => esc_url_raw(rest_url()),
            'nonce'   => wp_create_nonce('wp_rest'),
            'poll'    => true,
        ]);

        wp_localize_script('ap-event-chat-js', 'ArtPulseChatVars', [
            'event_id' => get_the_ID(),
        ]);

        wp_localize_script('ap-forum-js', 'APForum', [
            'rest_url'    => esc_url_raw(rest_url()),
            'nonce'       => wp_create_nonce('wp_rest'),
            'can_comment' => is_user_logged_in(),
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
            'dashboardUrl'  => self::get_user_dashboard_url(),
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
            'dashboardUrl'  => self::get_user_dashboard_url(),
        ]);

        wp_enqueue_script(
            'ap-artist-submission-js',
            plugins_url('assets/js/ap-artist-submission.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-artist-submission-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
            'dashboardUrl'  => self::get_user_dashboard_url(),
        ]);

        wp_enqueue_script(
            'ap-artwork-submission-js',
            plugins_url('assets/js/ap-artwork-submission.js', ARTPULSE_PLUGIN_FILE),
            ['wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_localize_script('ap-artwork-submission-js', 'APSubmission', [
            'endpoint'      => esc_url_raw(rest_url('artpulse/v1/submissions')),
            'mediaEndpoint' => esc_url_raw(rest_url('wp/v2/media')),
            'nonce'         => wp_create_nonce('wp_rest'),
            'dashboardUrl'  => self::get_user_dashboard_url(),
        ]);

        wp_enqueue_script(
            'ap-auth-js',
            plugins_url('assets/js/ap-auth.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0.0',
            true
        );

        wp_localize_script('ap-auth-js', 'APLogin', [
            'ajaxUrl'         => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('ap_login_nonce'),
            'orgSubmissionUrl'=> $this->get_org_submission_url(),
            'artistEndpoint'  => esc_url_raw(rest_url('artpulse/v1/artist-upgrade')),
            'restNonce'       => wp_create_nonce('wp_rest'),
            'dashboardUrl'    => self::get_user_dashboard_url(),
        ]);

        wp_enqueue_script(
            'ap-dashboard',
            plugins_url('assets/js/ap-dashboard.js', ARTPULSE_PLUGIN_FILE),
            ['wp-element'],
            '1.0.0',
            true
        );

        $user = wp_get_current_user();
        $role = $user->roles[0] ?? '';
        wp_localize_script('ap-dashboard', 'APDashboard', [
            'role' => $role,
        ]);

        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

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

    private function get_org_submission_url(): string
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

    public static function get_user_dashboard_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_user_dashboard]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    /**
     * Locate the page containing the organization dashboard shortcode.
     */
    public static function get_org_dashboard_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_org_dashboard]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    /**
     * Locate the page containing the artist dashboard shortcode.
     */
    public static function get_artist_dashboard_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_artist_dashboard]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    /**
     * Locate the page containing the login shortcode.
     */
    public static function get_login_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_login]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return wp_login_url();
    }

    /**
     * Locate the page containing the event submission shortcode and return its URL.
     */
    public static function get_event_submission_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_submit_event]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
    }

    /**
     * Locate the page containing the payouts shortcode.
     */
    public static function get_payouts_url(): string
    {
        $pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => '[ap_payouts]',
            'numberposts' => 1,
        ]);

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return home_url('/');
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

    public function maybe_migrate_profile_link_request_slug()
    {
        if (get_option('ap_profile_link_req_migrated')) {
            return;
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->posts,
            ['post_type' => 'ap_profile_link_req'],
            ['post_type' => 'ap_profile_link_request']
        );

        update_option('ap_profile_link_req_migrated', 1);
    }

    public function load_textdomain()
    {
        load_plugin_textdomain(
            'artpulse',
            false,
            dirname(plugin_basename(ARTPULSE_PLUGIN_FILE)) . '/languages'
        );
    }

    public function maybe_add_upload_cap()
    {
        if (get_option('ap_member_upload_cap_added')) {
            return;
        }

        require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
        \ArtPulse\Core\RoleSetup::install();

        update_option('ap_member_upload_cap_added', 1);
    }

    public function maybe_add_collection_cap()
    {
        if (get_option('ap_collection_cap_added')) {
            return;
        }

        require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
        \ArtPulse\Core\RoleSetup::assign_capabilities();

        update_option('ap_collection_cap_added', 1);
    }

    public function check_plugin_version(): void
    {
        $opts    = get_option('artpulse_settings', []);
        $stored  = $opts['plugin_version'] ?? null;
        if ($stored !== self::VERSION) {
            do_action('artpulse_upgrade', $stored, self::VERSION);
            $opts['plugin_version'] = self::VERSION;
            update_option('artpulse_settings', $opts);
        }
    }

    public static function add_defer(string $tag, string $handle, string $src): string
    {
        $defer = [
            'ap-membership-account-js',
            'ap-payouts-js',
            'ap-account-settings-js'
        ];
        if (in_array($handle, $defer, true)) {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }
}
