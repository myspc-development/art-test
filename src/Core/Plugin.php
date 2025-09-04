<?php

namespace ArtPulse\Core;

use ArtPulse\Core\PortfolioManager;
use ArtPulse\Core\AdminDashboard;
use ArtPulse\Core\DocumentationManager;
use ArtPulse\Rest\ArtistRestController;
use ArtPulse\Rest\CurrentUserController;
use ArtPulse\Rest\DashboardMessagesController;
use ArtPulse\Rest\MessagesController;
use ArtPulse\Rest\RestRelationships;
use ArtPulse\Rest\TaxonomyRestFilters;
use ArtPulse\Discovery\TrendingManager;
use ArtPulse\Rest\TrendingRestController;
use ArtPulse\Personalization\RecommendationPreferenceManager;
use ArtPulse\Admin\EngagementDashboard;
use ArtPulse\Core\ArtworkEventLinkManager;
use ArtPulse\Engagement\DigestMailer;
use ArtPulse\Frontend\WidgetEmbedShortcode;
use ArtPulse\Blocks\WidgetEmbedBlock;
use ArtPulse\Blocks\DashboardNewsBlock;
use ArtPulse\Blocks\RelatedProjectsBlock;
use ArtPulse\Core\DashboardBlockPatternManager;
use ArtPulse\Frontend\ReactDashboardShortcode;
use ArtPulse\Frontend\ShortcodeRoleDashboard;
use ArtPulse\Rest\UserLayoutController;
use ArtPulse\Marketplace\MarketplaceManager;
use ArtPulse\Marketplace\AuctionManager;
use ArtPulse\Community\ReferralManager;
use ArtPulse\Core\BadgeRules;
use ArtPulse\Core\VisitTracker;
use ArtPulse\Core\MultiOrgRoles;
use ArtPulse\Core\OrgInviteManager;
use ArtPulse\Core\OrgContext;
use ArtPulse\Monetization\EventBoostManager;
use ArtPulse\Core\ReportSubscriptionManager;
use ArtPulse\Reporting\OrgReportController;
use ArtPulse\AI\GrantAssistant;
use ArtPulse\Rest\VisitRestController;
use ArtPulse\Rest\OrgUserRolesController;
use ArtPulse\Rest\OrgRoleInviteController;
use ArtPulse\Rest\SystemStatusEndpoint;
use ArtPulse\Rest\OrgDirectoryController;
use ArtPulse\Core\FeedAccessLogger;
use ArtPulse\Admin\PortfolioToolsPage;
use ArtPulse\Admin\PortfolioSyncLogsPage;
use ArtPulse\Admin\RoleDashboardPage;
use ArtPulse\Core\PortfolioSyncLogger;
use ArtPulse\Core\OrgRoleMetaMigration;
use ArtPulse\Rest\NearbyEventsController;
use ArtPulse\Rest\EventListController;
use ArtPulse\Admin\Widgets\WebhooksWidget;
use ArtPulse\Admin\Widgets\WidgetManifestPanelWidget;
use ArtPulse\Admin\Widgets\WidgetStatusPanelWidget;

class Plugin {

	private const VERSION = '1.3.18';

	public static function register(): void {
		new self();
	}

	public function __construct() {
		$this->define_constants();
		add_action( 'init', array( $this, 'load_textdomain' ) );
		$this->register_hooks();
		add_action( 'plugins_loaded', array( $this, 'check_plugin_version' ) );
	}

	private function define_constants() {
		if ( ! defined( 'ARTPULSE_VERSION' ) ) {
			define( 'ARTPULSE_VERSION', self::VERSION );
		}
		if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
			define( 'ARTPULSE_PLUGIN_DIR', plugin_dir_path( dirname( __DIR__ ) ) );
		}
		if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
			define( 'ARTPULSE_PLUGIN_FILE', ARTPULSE_PLUGIN_DIR . 'artpulse-management.php' );
		}
		if ( ! defined( 'ARTPULSE_API_NAMESPACE' ) ) {
			define( 'ARTPULSE_API_NAMESPACE', 'artpulse/v1' );
		}
	}

	private function register_hooks() {
		register_activation_hook( ARTPULSE_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( ARTPULSE_PLUGIN_FILE, array( $this, 'deactivate' ) );

		add_action( 'init', array( $this, 'register_core_modules' ) );
		add_action( 'init', array( \ArtPulse\Frontend\SubmissionForms::class, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

                add_action( 'init', array( \ArtPulse\Rest\EventChatController::class, 'register' ) );
                \ArtPulse\Frontend\EventChatAssets::register();
                add_action( 'rest_api_init', array( \ArtPulse\Rest\RestRoutes::class, 'register_all' ) );
                SystemStatusEndpoint::register();
                OrgDirectoryController::register();
                add_action( 'init', array( \ArtPulse\Core\EventRsvpMetaMigration::class, 'maybe_migrate' ) );
                add_action( 'init', array( OrgRoleMetaMigration::class, 'maybe_migrate' ) );
		add_action( 'init', array( $this, 'maybe_migrate_org_meta' ) );
		add_action( 'init', array( $this, 'maybe_migrate_profile_link_request_slug' ) );
		add_action( 'init', array( $this, 'maybe_add_upload_cap' ) );
		add_action( 'init', array( $this, 'maybe_add_collection_cap' ) );
		add_action( 'init', array( \ArtPulse\Core\RoleSetup::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\FavoritesManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\FollowManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\ProfileLinkRequestManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\NotificationManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\DirectMessages::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\BlockedUsers::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\CommentReports::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Admin\LoginEventsPage::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\UserEngagementLogger::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\ProfileMetrics::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\EventMetrics::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\ArtworkEventLinkManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Personalization\RecommendationEngine::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Personalization\RecommendationPreferenceManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\ActivityLogger::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\DelegatedAccessManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\EventChatController::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Community\EventVoteManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\CompetitionEntryManager::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Core\FeedAccessLogger::class, 'maybe_install_table' ) );
		add_action( 'init', array( VisitTracker::class, 'maybe_install_table' ) );
		add_action( 'init', array( \ArtPulse\Frontend\CompetitionDashboardShortcode::class, 'register' ) );
		add_filter( 'script_loader_tag', array( self::class, 'add_defer' ), 10, 3 );
	}

	public function activate() {
		$db_version_option = 'artpulse_db_version';

		// Track installed plugin version
		update_option( 'ap_plugin_version', self::VERSION );

		// Init settings with defaults
		$defaults = function_exists( 'artpulse_get_default_settings' ) ? artpulse_get_default_settings() : array();
		$settings = get_option( 'artpulse_settings', array() );
		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $settings[ $key ] ) ) {
				$settings[ $key ] = $value;
			}
		}
		$settings['plugin_version'] = self::VERSION;
		update_option( 'artpulse_settings', $settings );

		// DB setup
		if ( get_option( $db_version_option ) !== self::VERSION ) {
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
			RecommendationPreferenceManager::install_table();
			\ArtPulse\Core\ActivityLogger::install_table();
			\ArtPulse\Community\CommentReports::install_table();
			\ArtPulse\Community\EventVoteManager::install_table();
			VisitTracker::install_table();
			TrendingManager::install_table();
			\ArtPulse\Core\FeedbackManager::install_table();
			\ArtPulse\Core\DelegatedAccessManager::install_table();
			\ArtPulse\Core\CompetitionEntryManager::install_table();
			\ArtPulse\Core\FeedAccessLogger::install_table();
			update_option( $db_version_option, self::VERSION );
		}

		// Register CPTs and flush rewrite rules
                \ArtPulse\Core\PostTypeRegistrar::register();
                \ArtPulse\Integration\CalendarExport::add_rewrite_rules();
                \ArtPulse\Frontend\EmbedRewrite::add_rules();
                \ArtPulse\Frontend\DashboardRoleRewrite::add_rules();
                \ArtPulse\Taxonomies\TaxonomiesRegistrar::register_event_types();
                \ArtPulse\Taxonomies\TaxonomiesRegistrar::maybe_insert_default_event_types();
                flush_rewrite_rules();

		// ✅ Fix: ensure roles/caps are installed
		require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
		\ArtPulse\Core\RoleSetup::install();
		$role = get_role( 'artist' );
		$role?->add_cap( 'ap_send_messages' );

		// Schedule cron
		if ( ! wp_next_scheduled( 'ap_daily_expiry_check' ) ) {
			wp_schedule_event( time(), 'daily', 'ap_daily_expiry_check' );
		}

		\ArtPulse\Engagement\DigestMailer::schedule_cron();
	}

	public function deactivate() {
		flush_rewrite_rules();
		wp_clear_scheduled_hook( 'ap_daily_expiry_check' );
		wp_clear_scheduled_hook( 'ap_send_digests' );
		wp_clear_scheduled_hook( 'ap_process_scheduled_messages' );
	}

	public function register_core_modules() {
		\ArtPulse\Core\PostTypeRegistrar::register(); // ✅ CPTs
		\ArtPulse\Core\MetaBoxRegistrar::register();
		\ArtPulse\Core\ShortcodeManager::register();
		\ArtPulse\Admin\SettingsPage::register();
		\ArtPulse\Admin\ShortcodePages::register();
		\ArtPulse\Core\MembershipManager::register();
		\ArtPulse\Core\AccessControlManager::register();
		\ArtPulse\Core\DashboardWidgetManager::register();
		\ArtPulse\Core\UserDashboardManager::register();
		\ArtPulse\Core\CapabilitiesManager::register();
		\ArtPulse\Curator\CuratorManager::register();
		WebhooksWidget::register();
		WidgetManifestPanelWidget::register();
		WidgetStatusPanelWidget::register();
		\ArtPulse\Core\AdminAccessManager::register();
		\ArtPulse\Core\LoginRedirectManager::register();
		\ArtPulse\Core\DirectoryManager::register();
		\ArtPulse\Core\AnalyticsManager::register();
		\ArtPulse\Core\AnalyticsDashboard::register();
		\ArtPulse\Admin\PaymentAnalyticsDashboard::register();
		\ArtPulse\Admin\PaymentReportsPage::register();
		\ArtPulse\Admin\TicketTiersPage::register();
		\ArtPulse\Admin\MembershipLevelsPage::register();
		\ArtPulse\Admin\WebhookLogsPage::register();
		EngagementDashboard::register();
		AdminDashboard::register();
		RoleDashboardPage::register();
		ShortcodeRoleDashboard::register();
		UserLayoutController::register();
		\ArtPulse\Rest\DashboardSeenController::register();
		\ArtPulse\Core\FrontendMembershipPage::register();
		\ArtPulse\Community\ProfileLinkRequestManager::register();
		\ArtPulse\Community\ArtistUpgradeRestController::register();
		\ArtPulse\Core\MyFollowsShortcode::register();
		\ArtPulse\Core\NotificationShortcode::register();
		\ArtPulse\Community\UserPreferencesRestController::register();
		\ArtPulse\Rest\WidgetSettingsRestController::register();
		\ArtPulse\Rest\DashboardConfigController::register();
		\ArtPulse\Rest\RoleWidgetMapController::register();
		\ArtPulse\Rest\LayoutSaveEndpoint::register();
		\ArtPulse\Rest\WidgetEditorController::register();
		\ArtPulse\Admin\DashboardLayoutEndpoint::register();
		\ArtPulse\DashboardBuilder\DashboardManager::register();
		\ArtPulse\Rest\DashboardWidgetController::register();
		\ArtPulse\Core\ProfileMetrics::register();
		\ArtPulse\Core\RoleAuditLogger::register();
		OrgContext::register();
		MultiOrgRoles::register();
		OrgInviteManager::register();
		\ArtPulse\Core\OrgDashboardManager::register();
		\ArtPulse\Core\ActivityLogger::register();
		FeedAccessLogger::register();
		\ArtPulse\Community\CommunityRoles::register();
		\ArtPulse\Core\DelegatedAccessManager::register();
		\ArtPulse\Admin\AdminListSorting::register();
		\ArtPulse\Rest\RestSortingSupport::register();
		\ArtPulse\Admin\AdminListColumns::register();
		\ArtPulse\Admin\EnqueueAssets::register();
		\ArtPulse\Frontend\Shortcodes::register();
		\ArtPulse\Frontend\MyEventsShortcode::register();
		\ArtPulse\Frontend\EventSubmissionShortcode::register();
		\ArtPulse\Frontend\EditEventShortcode::register();
		\ArtPulse\Frontend\OrganizationEventForm::register();
		\ArtPulse\Frontend\OrganizationSubmissionForm::register();
		\ArtPulse\Frontend\SubmitArtistForm::register();
		\ArtPulse\Frontend\UserProfileShortcode::register();
		\ArtPulse\Frontend\ProfileEditShortcode::register();
		\ArtPulse\Frontend\ArtistProfileFormShortcode::register();
		\ArtPulse\Frontend\OrgProfileEditShortcode::register();
		\ArtPulse\Frontend\OrgPublicProfileShortcode::register();
		\ArtPulse\Frontend\PayoutsPage::register();
		\ArtPulse\Frontend\AccountSettingsPage::register();
		\ArtPulse\Frontend\PortfolioBuilder::register();
		PortfolioManager::register();
		\ArtPulse\Core\PortfolioSyncLogger::register();
		\ArtPulse\Admin\PortfolioToolsPage::register();
		\ArtPulse\Admin\PortfolioSyncLogsPage::register();
		\ArtPulse\Integration\PortfolioSync::register();
		\ArtPulse\Integration\PortfolioMigration::register();
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
		\ArtPulse\Frontend\EventCardShortcode::register();
		\ArtPulse\Frontend\EventListShortcode::register();
		\ArtPulse\Frontend\EventCalendarShortcode::register();
		\ArtPulse\Rest\CalendarFeedController::register();
		\ArtPulse\Frontend\EventMapShortcode::register();
		\ArtPulse\Frontend\EventCommentsShortcode::register();
		\ArtPulse\Frontend\ArtworkCommentsShortcode::register();
		\ArtPulse\Frontend\EventChatShortcode::register();
		\ArtPulse\Frontend\MessagesShortcode::register();
		\ArtPulse\Frontend\InboxReact::register();
		\ArtPulse\Frontend\ReactDashboardShortcode::register();
		\ArtPulse\Frontend\RestListShortcodes::register();
		\ArtPulse\Frontend\CollectionsShortcode::register();
		\ArtPulse\Frontend\CuratorProfileShortcode::register();
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
		\ArtPulse\Admin\OrgCommunicationsCenter::register();
		\ArtPulse\Admin\ScheduledMessageManager::register();
		\ArtPulse\Admin\PostStatusRejected::register();
		\ArtPulse\Rest\OrgUserRolesController::register();
		OrgRoleInviteController::register();
		\ArtPulse\Rest\LocationRestController::register();
		\ArtPulse\Rest\OrgAnalyticsController::register();
		\ArtPulse\Rest\OrgDashboardController::register();
		\ArtPulse\Rest\ProfileMetricsController::register();
		\ArtPulse\Rest\AnalyticsRestController::register();
		\ArtPulse\Rest\AnalyticsPilotController::register();
		\ArtPulse\Rest\ShareController::register();
		\ArtPulse\Rest\PaymentReportsController::register();
		\ArtPulse\Rest\UserAccountRestController::register();
		\ArtPulse\Rest\CollectionRestController::register();
		\ArtPulse\Rest\ArtworkAuctionController::register();
		\ArtPulse\Rest\EventListController::register();
		\ArtPulse\Rest\NearbyEventsController::register();
		\ArtPulse\Rest\SpotlightRestController::register();
		\ArtPulse\Rest\SpotlightAnalyticsController::register();
		\ArtPulse\Rest\CurrentUserController::register();
		\ArtPulse\Rest\DashboardMessagesController::register();
		\ArtPulse\Rest\MessagesController::register();
		\ArtPulse\Rest\CuratorRestController::register();
		\ArtPulse\Rest\ProfileVerificationController::register();
		\ArtPulse\Rest\CommunityAnalyticsController::register();
		ArtistRestController::register();
		RestRelationships::register();
		TaxonomyRestFilters::register();

		\ArtPulse\Admin\MetaBoxesArtist::register();
		\ArtPulse\Admin\MetaBoxesArtwork::register();
		\ArtPulse\Admin\MetaBoxesEvent::register();
		\ArtPulse\Admin\MetaBoxesCollection::register();
		\ArtPulse\Admin\EventRsvpToggle::register();
		\ArtPulse\Admin\MetaBoxesAddress::register( array( 'artpulse_event' ) );
		\ArtPulse\Admin\MetaBoxesOrganisation::register();

		\ArtPulse\Admin\AdminColumnsArtist::register();
		\ArtPulse\Admin\AdminColumnsArtwork::register();
		\ArtPulse\Admin\AdminColumnsEvent::register();
		\ArtPulse\Admin\AdminColumnsOrganisation::register();
		\ArtPulse\Admin\QuickStartGuide::register();
		\ArtPulse\Admin\ReleaseNotes::register();
		\ArtPulse\Admin\DashboardPageCheck::register();
		DocumentationManager::register();
		\ArtPulse\Taxonomies\TaxonomiesRegistrar::register();
		\ArtPulse\Core\EventExpiryCron::register();
		\ArtPulse\Core\EventViewCounter::register();
		\ArtPulse\Core\EventTemplateManager::register();

		if ( class_exists( '\\ArtPulse\\Ajax\\FrontendFilterHandler' ) ) {
			\ArtPulse\Ajax\FrontendFilterHandler::register();
		}

		$opts = get_option( 'artpulse_settings', array() );
		if ( ! empty( $opts['woocommerce_enabled'] ) ) {
			\ArtPulse\Core\WooCommerceIntegration::register();
			\ArtPulse\Core\PurchaseShortcode::register();
			\ArtPulse\Core\ArtworkWooSync::register();
		}

		\ArtPulse\Admin\AdminColumnsTaxonomies::register();
		\ArtPulse\Admin\MemberEnhancements::register();
		\ArtPulse\Admin\EventNotesTasks::register();
		\ArtPulse\Admin\ProfileLinkRequestAdmin::register();
		add_action( 'admin_menu', array( \ArtPulse\Admin\OrgDashboardAdmin::class, 'register' ) );
		add_action( 'save_post', array( \ArtPulse\Admin\OrgDashboardAdmin::class, 'clear_cache' ), 10, 3 );
		\ArtPulse\Blocks\AdvancedTaxonomyFilterBlock::register();
		\ArtPulse\Blocks\AjaxFilterBlock::register();
		\ArtPulse\Blocks\FilteredListShortcodeBlock::register();
		\ArtPulse\Blocks\SpotlightBlock::register();
		\ArtPulse\Blocks\FavoritePortfolioBlock::register();
		\ArtPulse\Blocks\TaxonomyFilterBlock::register();
		\ArtPulse\Blocks\PortfolioPreviewBlock::register();
		\ArtPulse\Blocks\EventCardBlock::register();
		\ArtPulse\Blocks\EventListBlock::register();
		\ArtPulse\Blocks\DashboardNewsBlock::register();
		RelatedProjectsBlock::register();
		WidgetEmbedShortcode::register();
		WidgetEmbedBlock::register();
		\ArtPulse\Blocks\FavoritesWidgetBlock::register();
		\ArtPulse\Frontend\WidgetsController::register();
		DashboardBlockPatternManager::register();
		\ArtPulse\Frontend\DashboardRoleRewrite::register();
		\ArtPulse\Frontend\EmbedRewrite::register();
		\ArtPulse\Analytics\EmbedAnalytics::register();
		\ArtPulse\Community\FollowRestController::register();
		\ArtPulse\Community\ProfileLinkRequestRestController::register();
		\ArtPulse\Community\NotificationRestController::register();
		\ArtPulse\Community\NotificationHooks::register();
		\ArtPulse\Community\DirectMessages::register();
		\ArtPulse\Community\EventVoteManager::register();
		\ArtPulse\Community\EventVoteRestController::register();
		\ArtPulse\Core\MembershipNotifier::register();
		\ArtPulse\Core\MembershipCron::register();
		\ArtPulse\Engagement\DigestMailer::register();
		\ArtPulse\Core\FeedbackManager::register();
		\ArtPulse\Frontend\FeedbackWidget::register();
		\ArtPulse\Core\DashboardFeedbackManager::register();
		\ArtPulse\Core\DashboardAnalyticsLogger::maybe_install_table();
		\ArtPulse\Rest\DashboardAnalyticsController::register();
		\ArtPulse\Frontend\NewsletterOptinEndpoint::register();
		\ArtPulse\Community\QaThreadPostType::register();
		\ArtPulse\Community\QaThreadRestController::register();
		\ArtPulse\Frontend\QaThreadShortcode::register();
		\ArtPulse\Admin\FeedbackPage::register();
		\ArtPulse\Admin\ChatModerationPage::register();
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
		\ArtPulse\Monetization\DonationLink::register();
		\ArtPulse\Monetization\DonationManager::register();
		\ArtPulse\Monetization\TipManager::register();
		ReferralManager::register();
		BadgeRules::register();
		\ArtPulse\Marketplace\MarketplaceManager::register();
		\ArtPulse\Marketplace\AuctionManager::register();
		\ArtPulse\Marketplace\PromotionManager::register();
		\ArtPulse\Integration\WebhookManager::register();
		\ArtPulse\Monetization\EventPromotionManager::register();
		\ArtPulse\Search\MetaFullTextSearch::register();
		\ArtPulse\Search\ExternalSearch::register();
		TrendingManager::register();
		\ArtPulse\Discovery\EventRankingManager::register();
		TrendingRestController::register();
		\ArtPulse\Personalization\RecommendationRestController::register();
		\ArtPulse\Rest\FollowVenueCuratorController::register();
		\ArtPulse\Personalization\WeeklyRecommendations::register();
		EventBoostManager::register();
		\ArtPulse\Admin\SnapshotPage::register();
		ReportSubscriptionManager::register();
		\ArtPulse\Reporting\OrgReportController::register();
		\ArtPulse\Reporting\SnapshotExportController::register();
		GrantAssistant::register();
		\ArtPulse\Rest\GrantReportController::register();
		\ArtPulse\Rest\OrgMetaController::register();
		\ArtPulse\Rest\EventManagementController::register();
	}

	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'ap-membership-account-js',
			plugins_url( 'assets/js/ap-membership-account.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-payouts-js',
			plugins_url( 'assets/js/ap-payouts.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-account-settings-js',
			plugins_url( 'assets/js/ap-account-settings.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-payouts-js',
			'APPayouts',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'  => array(
					'balanceLabel' => __( 'Current Balance', 'artpulse' ),
					'noHistory'    => __( 'No payouts found.', 'artpulse' ),
					'updated'      => __( 'Settings updated.', 'artpulse' ),
				),
			)
		);

		wp_localize_script(
			'ap-account-settings-js',
			'APAccountSettings',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'  => array( 'saved' => __( 'Settings saved.', 'artpulse' ) ),
			)
		);

               \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-favorites-js', 'assets/js/ap-favorites.js', array(), true, array( 'type' => 'module' ) );

               \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-follow-feed-js', 'assets/js/favorites.js', array( 'wp-api-fetch' ) );

		wp_localize_script(
			'ap-follow-feed-js',
			'ArtPulseFavoritesFeed',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_enqueue_script(
			'ap-discovery-feed',
			plugins_url( 'assets/js/components/DiscoveryFeed.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-element' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-discovery-feed',
			'APDiscoveryFeed',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_enqueue_script(
			'ap-notifications-js',
			plugins_url( 'assets/js/ap-notifications.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-messages-js',
			plugins_url( 'assets/js/ap-messages.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-event-comments-js',
			plugins_url( 'assets/js/ap-event-comments.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-comment-js',
			plugins_url( 'assets/js/comment.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		$event_id = 0;
		if ( isset( $_GET['event_id'] ) ) {
			$event_id = (int) $_GET['event_id'];
		}
		if ( ! $event_id && is_singular( 'artpulse_event' ) ) {
			$event_id = (int) get_queried_object_id();
		}
		if ( $event_id ) {
			wp_enqueue_script(
				'ap-event-chat-js',
				plugins_url( 'assets/js/ap-event-chat.js', ARTPULSE_PLUGIN_FILE ),
				array( 'wp-api-fetch' ),
				'1.0.0',
				true
			);
		}

		wp_enqueue_script(
			'ap-event-vote-js',
			plugins_url( 'assets/js/ap-event-vote.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-rsvp-js',
			plugins_url( 'assets/js/rsvp.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-qa-thread',
			plugins_url( 'assets/js/ap-qa-thread.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-react-widgets',
			plugins_url( 'assets/js/react-widgets.bundle.js', ARTPULSE_PLUGIN_FILE ),
			array( 'react', 'react-dom', 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-forum-js',
			plugins_url( 'assets/js/ap-forum.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-element', 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'ap-follow-js',
			plugins_url( 'assets/js/follow.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-follow-js',
			'APFollow',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'followText'   => __( 'Follow', 'artpulse' ),
				'unfollowText' => __( 'Unfollow', 'artpulse' ),
			)
		);

		wp_enqueue_script(
			'ap-donations-js',
			plugins_url( 'assets/js/donations.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-donations-js',
			'APDonations',
			array(
				'root'   => esc_url_raw( rest_url() ),
				'nonce'  => wp_create_nonce( 'wp_rest' ),
				'thanks' => __( 'Thank you for your support!', 'artpulse' ),
			)
		);

		wp_localize_script(
			'ap-notifications-js',
			'APNotifications',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
		wp_localize_script(
			'ap-notifications-js',
			'APNotifyData',
			array(
				'rest_url' => esc_url_raw( rest_url() ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-messages-js',
			'APMessages',
			array(
				'apiRoot'  => esc_url_raw( rest_url() ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'pollId'   => 0,
				'loggedIn' => is_user_logged_in(),
			)
		);

		wp_localize_script(
			'ap-event-comments-js',
			'APComments',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-comment-js',
			'APArtistComments',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-event-vote-js',
			'APEventVote',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-rsvp-js',
			'APRsvp',
			array(
				'root'         => esc_url_raw( rest_url() ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'rsvpText'     => __( 'RSVP', 'artpulse' ),
				'cancelText'   => __( 'Cancel RSVP', 'artpulse' ),
				'goingText'    => __( "You're going", 'artpulse' ),
				'waitlistText' => __( 'Added to waitlist', 'artpulse' ),
				'limitText'    => __( 'RSVP limit reached', 'artpulse' ),
			)
		);

		wp_localize_script(
			'ap-qa-thread',
			'APQa',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-react-widgets',
			'APTickets',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_localize_script(
			'ap-forum-js',
			'APForum',
			array(
				'rest_url'    => esc_url_raw( rest_url() ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'can_comment' => is_user_logged_in(),
				'can_start'   => \ArtPulse\Community\CommunityRoles::can_post_thread( get_current_user_id() ),
				'can_tag'     => \ArtPulse\Community\CommunityRoles::can_tag( get_current_user_id() ),
			)
		);

		wp_enqueue_script(
			'ap-submission-form-js',
			plugins_url( 'assets/js/ap-submission-form.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-submission-form-js',
			'APSubmission',
			array(
				'endpoint'      => esc_url_raw( rest_url( 'artpulse/v1/submissions' ) ),
				'mediaEndpoint' => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
                               'dashboardUrl'  => self::get_user_dashboard_url(),
			)
		);

		wp_enqueue_script(
			'ap-org-submission-js',
			plugins_url( 'assets/js/ap-org-submission.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-org-submission-js',
			'APSubmission',
			array(
				'endpoint'      => esc_url_raw( rest_url( 'artpulse/v1/submissions' ) ),
				'mediaEndpoint' => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
                               'dashboardUrl'  => self::get_org_dashboard_url(),
			)
		);

		wp_enqueue_script(
			'ap-artist-submission-js',
			plugins_url( 'assets/js/ap-artist-submission.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-artist-submission-js',
			'APSubmission',
			array(
				'endpoint'      => esc_url_raw( rest_url( 'artpulse/v1/submissions' ) ),
				'mediaEndpoint' => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
                               'dashboardUrl'  => self::get_artist_dashboard_url(),
			)
		);

		wp_enqueue_script(
			'ap-artwork-submission-js',
			plugins_url( 'assets/js/ap-artwork-submission.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-artwork-submission-js',
			'APSubmission',
			array(
				'endpoint'      => esc_url_raw( rest_url( 'artpulse/v1/submissions' ) ),
				'mediaEndpoint' => esc_url_raw( rest_url( 'wp/v2/media' ) ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'dashboardUrl'  => self::get_user_dashboard_url(),
			)
		);

		wp_enqueue_script(
			'ap-auth-js',
			plugins_url( 'assets/js/ap-auth.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-auth-js',
			'APLogin',
			array(
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'ap_login_nonce' ),
				'orgSubmissionUrl' => $this->get_org_submission_url(),
				'artistEndpoint'   => esc_url_raw( rest_url( 'artpulse/v1/artist-upgrade' ) ),
				'restNonce'        => wp_create_nonce( 'wp_rest' ),
				'dashboardUrl'     => self::get_user_dashboard_url(),
			)
		);

		// The custom React sidebar has been removed. WordPress menus are used instead.

		// The standalone dashboard script has been removed.

		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}

		$opts = get_option( 'artpulse_settings', array() );
		if ( ! empty( $opts['service_worker_enabled'] ) ) {
			wp_enqueue_script(
				'ap-sw-loader',
				plugins_url( 'assets/js/sw-loader.js', ARTPULSE_PLUGIN_FILE ),
				array(),
				'1.0.0',
				true
			);

			wp_localize_script(
				'ap-sw-loader',
				'APServiceWorker',
				array(
					'url'     => plugins_url( 'assets/js/service-worker.js', ARTPULSE_PLUGIN_FILE ),
					'enabled' => true,
				)
			);

			wp_enqueue_script(
				'ap-a2hs',
				plugins_url( 'assets/js/a2hs.js', ARTPULSE_PLUGIN_FILE ),
				array(),
				'1.0.0',
				true
			);
		}

		wp_enqueue_script(
			'ap-dashboard-feedback',
			plugins_url( 'assets/js/dashboard-feedback-widget.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-dashboard-feedback',
			'APDashFeedback',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ap_dashboard_feedback' ),
				'thanks'  => __( 'Thanks for your feedback!', 'artpulse' ),
			)
		);

		wp_enqueue_script(
			'ap-dashboard-analytics',
			plugins_url( 'assets/js/dashboard-analytics.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ap-dashboard-analytics',
			'APDashAnalytics',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	private function get_org_submission_url(): string {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				's'           => '[ap_submit_organization]',
				'numberposts' => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0]->ID );
		}

		return home_url( '/' );
	}

       public static function get_user_dashboard_url(): string {
               return home_url( '/dashboard/user' );
       }

	/**
	 * Locate the page containing the organization dashboard shortcode.
	 */
       public static function get_org_dashboard_url(): string {
               return home_url( '/dashboard/org' );
       }

	/**
	 * Locate the page containing the artist dashboard shortcode.
	 */
       public static function get_artist_dashboard_url(): string {
               return home_url( '/dashboard/artist' );
       }

	/**
	 * Locate the page containing the login shortcode.
	 */
	public static function get_login_url(): string {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				's'           => '[ap_login]',
				'numberposts' => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			$permalink = get_permalink( $pages[0]->ID );
			if ( $permalink ) {
				return $permalink;
			}
		}

		return wp_login_url();
	}

	/**
	 * Locate the page containing the event submission shortcode and return its URL.
	 */
	public static function get_event_submission_url(): string {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				's'           => '[ap_submit_event]',
				'numberposts' => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0]->ID );
		}

		return home_url( '/' );
	}

	/**
	 * Locate the page containing the payouts shortcode.
	 */
	public static function get_payouts_url(): string {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				's'           => '[ap_payouts]',
				'numberposts' => 1,
			)
		);

		if ( ! empty( $pages ) ) {
			return get_permalink( $pages[0]->ID );
		}

		return home_url( '/' );
	}

	public function maybe_migrate_org_meta() {
		if ( get_option( 'ap_org_meta_prefix' ) === 'ead_org' ) {
			return;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'artpulse_org',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_ap_org_address',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => '_ap_org_website',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $posts as $post_id ) {
			$address = get_post_meta( $post_id, '_ap_org_address', true );
			if ( $address && ! get_post_meta( $post_id, 'ead_org_street_address', true ) ) {
				update_post_meta( $post_id, 'ead_org_street_address', $address );
			}

			$website = get_post_meta( $post_id, '_ap_org_website', true );
			if ( $website && ! get_post_meta( $post_id, 'ead_org_website_url', true ) ) {
				update_post_meta( $post_id, 'ead_org_website_url', $website );
			}
		}

		update_option( 'ap_org_meta_prefix', 'ead_org' );
	}

	public function maybe_migrate_profile_link_request_slug() {
		if ( get_option( 'ap_profile_link_req_migrated' ) ) {
			return;
		}

		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_type' => 'ap_profile_link_req' ),
			array( 'post_type' => 'ap_profile_link_request' )
		);

		update_option( 'ap_profile_link_req_migrated', 1 );
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'artpulse',
			false,
			dirname( plugin_basename( ARTPULSE_PLUGIN_FILE ) ) . '/languages'
		);
	}

	public function maybe_add_upload_cap() {
		if ( get_option( 'ap_member_upload_cap_added' ) ) {
			return;
		}

		require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
		\ArtPulse\Core\RoleSetup::install();

		update_option( 'ap_member_upload_cap_added', 1 );
	}

	public function maybe_add_collection_cap() {
		if ( get_option( 'ap_collection_cap_added' ) ) {
			return;
		}

		require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
		\ArtPulse\Core\RoleSetup::assign_capabilities();

		update_option( 'ap_collection_cap_added', 1 );
	}

	public function check_plugin_version(): void {
		$opts   = get_option( 'artpulse_settings', array() );
		$stored = $opts['plugin_version'] ?? null;
		if ( $stored !== self::VERSION ) {
			do_action( 'artpulse_upgrade', $stored, self::VERSION );
			$opts['plugin_version'] = self::VERSION;
			update_option( 'artpulse_settings', $opts );
		}
	}

	public static function add_defer( string $tag, string $handle, string $src ): string {
		$defer = array(
			'ap-membership-account-js',
			'ap-payouts-js',
			'ap-account-settings-js',
		);
		if ( in_array( $handle, $defer, true ) ) {
			return str_replace( ' src', ' defer src', $tag );
		}
		return $tag;
	}
}
