<?php
namespace ArtPulse\Admin;

use ArtPulse\Admin\ImportExportTab;
use ArtPulse\Admin\SettingsRegistry;
use ArtPulse\Admin\FieldRenderer;
use ArtPulse\Admin\ConfigBackupTab;
use ArtPulse\Admin\UpdatesTab;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\ActivityLogger;
use ArtPulse\Support\WpAdminFns;

class SettingsPage {

	public static function register() {
		self::bootstrap_settings();
		ConfigBackupTab::register();
		UpdatesTab::register();
		DashboardWidgetTools::register();
		add_action( 'admin_menu', array( self::class, 'addMenu' ) );
		add_action( 'admin_init', array( self::class, 'registerSettings' ) );
		add_action( 'wp_login', array( self::class, 'trackLastLogin' ), 10, 2 );
		add_action( 'wp_logout', array( self::class, 'trackLastLogout' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueueAdminAssets' ) );
	}

	private static function bootstrap_settings(): void {
		SettingsRegistry::register_tab( 'general', __( 'General', 'artpulse' ) );
		SettingsRegistry::register_tab( 'location', __( 'Location APIs', 'artpulse' ) );
		SettingsRegistry::register_tab( 'import_export', __( 'Import/Export', 'artpulse' ) );
		SettingsRegistry::register_tab( 'config_backup', __( 'Config Backup', 'artpulse' ) );
		SettingsRegistry::register_tab( 'shortcodes', __( 'Shortcode Pages', 'artpulse' ) );
		SettingsRegistry::register_tab( 'search', __( 'Search', 'artpulse' ) );
		SettingsRegistry::register_tab( 'emails', __( 'Email Delivery', 'artpulse' ) );
		SettingsRegistry::register_tab( 'updates', __( 'Updates', 'artpulse' ) );
		SettingsRegistry::register_tab( 'widgets', __( 'Widget Editor', 'artpulse' ) );
		SettingsRegistry::register_tab( 'monetization', __( 'Monetization', 'artpulse' ) );

		$general_fields = array(
			'basic_fee'                  => array(
				'label' => __( 'Basic Member Fee ($)', 'artpulse' ),
				'desc'  => __( 'Monthly cost for Basic members. Leave blank to disable.', 'artpulse' ),
				'type'  => 'text',
			),
			'pro_fee'                    => array(
				'label' => __( 'Pro Artist Fee ($)', 'artpulse' ),
				'desc'  => __( 'Subscription price for Pro Artists.', 'artpulse' ),
				'type'  => 'text',
			),
			'org_fee'                    => array(
				'label' => __( 'Organization Fee ($)', 'artpulse' ),
				'desc'  => __( 'Fee charged to organizations.', 'artpulse' ),
				'type'  => 'text',
			),
			'currency'                   => array(
				'label' => __( 'Currency (ISO)', 'artpulse' ),
				'desc'  => __( '3-letter currency code (e.g., USD, EUR, GBP).', 'artpulse' ),
				'type'  => 'text',
			),
			'stripe_enabled'             => array(
				'label' => __( 'Enable Stripe Integration', 'artpulse' ),
				'desc'  => __( 'Enable Stripe to manage payments and subscriptions.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'stripe_pub_key'             => array(
				'label' => __( 'Stripe Publishable Key', 'artpulse' ),
				'desc'  => __( 'Used for client-side Stripe operations.', 'artpulse' ),
				'type'  => 'text',
			),
			'stripe_secret'              => array(
				'label' => __( 'Stripe Secret Key', 'artpulse' ),
				'desc'  => __( 'Used for secure server-side API calls to Stripe.', 'artpulse' ),
				'type'  => 'text',
			),
			'stripe_webhook_secret'      => array(
				'label' => __( 'Stripe Webhook Secret', 'artpulse' ),
				'desc'  => __( 'Secret used to verify webhook calls from Stripe.', 'artpulse' ),
				'type'  => 'text',
			),
			'woocommerce_enabled'        => array(
				'label' => __( 'Enable WooCommerce Integration', 'artpulse' ),
				'desc'  => __( 'Use WooCommerce products for membership purchases.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'payment_metrics_cache'      => array(
				'label' => __( 'Payment Metrics Cache (minutes)', 'artpulse' ),
				'desc'  => __( 'How long to cache payment analytics data.', 'artpulse' ),
				'type'  => 'number',
			),
			'service_worker_enabled'     => array(
				'label' => __( 'Enable Service Worker', 'artpulse' ),
				'desc'  => __( 'Adds a service worker for basic offline caching.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'openai_api_key'             => array(
				'label' => __( 'OpenAI API Key', 'artpulse' ),
				'desc'  => __( 'Used for auto-tagging and summaries.', 'artpulse' ),
				'type'  => 'text',
			),
			'external_api_base_url'      => array(
				'label' => __( 'External API Base URL', 'artpulse' ),
				'desc'  => __( 'Base URL for external dashboard APIs.', 'artpulse' ),
				'type'  => 'text',
			),
			'external_api_token'         => array(
				'label' => __( 'External API Token', 'artpulse' ),
				'desc'  => __( 'Bearer token used for external API requests.', 'artpulse' ),
				'type'  => 'text',
			),
			'oauth_google_enabled'       => array(
				'label' => __( 'Enable Google Login', 'artpulse' ),
				'desc'  => __( 'Show Google button on the login form.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'oauth_facebook_enabled'     => array(
				'label' => __( 'Enable Facebook Login', 'artpulse' ),
				'desc'  => __( 'Show Facebook button on the login form.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'oauth_apple_enabled'        => array(
				'label' => __( 'Enable Apple Login', 'artpulse' ),
				'desc'  => __( 'Show Apple button on the login form.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'enforce_two_factor'         => array(
				'label' => __( 'Enforce Two-Factor', 'artpulse' ),
				'desc'  => __( 'Require users to enable two-factor authentication before logging in.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'override_artist_membership' => array(
				'label' => __( 'Override Artist Membership', 'artpulse' ),
				'desc'  => __( 'Allow administrators to bypass membership requirements and fees for artists.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'override_org_membership'    => array(
				'label' => __( 'Override Organization Membership', 'artpulse' ),
				'desc'  => __( 'Allow administrators to bypass membership requirements and fees for organizations.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'override_member_membership' => array(
				'label' => __( 'Override Member Membership', 'artpulse' ),
				'desc'  => __( 'Allow administrators to bypass membership requirements and fees for regular members.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'auto_expire_events'         => array(
				'label' => __( 'Auto-expire Past Events', 'artpulse' ),
				'desc'  => __( 'Move events to Draft when the end date has passed.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'enable_artworks_for_sale'   => array(
				'label' => __( 'Enable Artworks for Sale', 'artpulse' ),
				'desc'  => __( 'Allow artworks to be marked for sale.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'disable_styles'             => array(
				'label' => __( 'Disable Plugin Styles', 'artpulse' ),
				'desc'  => __( 'Do not load ArtPulse CSS on the frontend.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'default_rsvp_limit'         => array(
				'label' => __( 'Default RSVP Limit', 'artpulse' ),
				'desc'  => __( 'Pre-filled limit for new events.', 'artpulse' ),
				'type'  => 'number',
			),
			'min_rsvp_limit'             => array(
				'label' => __( 'Minimum RSVP Limit', 'artpulse' ),
				'desc'  => __( 'Lowest allowed RSVP limit.', 'artpulse' ),
				'type'  => 'number',
			),
			'max_rsvp_limit'             => array(
				'label' => __( 'Maximum RSVP Limit', 'artpulse' ),
				'desc'  => __( 'Highest allowed RSVP limit.', 'artpulse' ),
				'type'  => 'number',
			),
			'waitlists_enabled'          => array(
				'label' => __( 'Enable Waitlists', 'artpulse' ),
				'desc'  => __( 'Allow events to use waitlists.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'default_email_template'     => array(
				'label' => __( 'Default Email Template', 'artpulse' ),
				'desc'  => __( 'HTML with placeholders like {{content}}', 'artpulse' ),
				'type'  => 'textarea',
			),
			'keep_data_on_uninstall'     => array(
				'label' => __( 'Keep Data on Uninstall', 'artpulse' ),
				'desc'  => __( 'Preserve settings and custom tables when removing the plugin.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'enable_wp_admin_access'     => array(
				'label' => __( 'Allow wp-admin Access', 'artpulse' ),
				'desc'  => __( 'Permit non-admin roles to access the default WordPress dashboard.', 'artpulse' ),
				'type'  => 'checkbox',
			),
			'dashboard_v2'               => array(
				'label'   => __( 'Roles Dashboard v2', 'artpulse' ),
				'desc'    => __( 'Enable the updated roles dashboard layout.', 'artpulse' ),
				'type'    => 'checkbox',
				'default' => 1,
			),
		);

		foreach ( $general_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'general', $key, $cfg );
		}

		$search_fields = array(
			'search_service'    => array(
				'label'   => __( 'Search Provider', 'artpulse' ),
				'desc'    => __( 'Select Algolia or ElasticPress.', 'artpulse' ),
				'type'    => 'select',
				'options' => array(
					''             => __( 'None', 'artpulse' ),
					'algolia'      => 'Algolia',
					'elasticpress' => 'ElasticPress',
				),
			),
			'algolia_app_id'    => array(
				'label' => __( 'Algolia App ID', 'artpulse' ),
				'desc'  => __( 'Your Algolia application ID.', 'artpulse' ),
				'type'  => 'text',
			),
			'algolia_api_key'   => array(
				'label' => __( 'Algolia API Key', 'artpulse' ),
				'desc'  => __( 'Search-only API key.', 'artpulse' ),
				'type'  => 'text',
			),
			'elasticpress_host' => array(
				'label' => __( 'ElasticPress Host', 'artpulse' ),
				'desc'  => __( 'Elasticsearch endpoint URL.', 'artpulse' ),
				'type'  => 'text',
			),
		);
		foreach ( $search_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'search', $key, $cfg );
		}

		$location_fields = array(
			'geonames_username' => array(
				'label' => __( 'Geonames Username', 'artpulse' ),
				'desc'  => __( 'Username for querying the Geonames API.', 'artpulse' ),
				'type'  => 'text',
			),
			'google_places_key' => array(
				'label' => __( 'Google Places API Key', 'artpulse' ),
				'desc'  => __( 'Key for Google Places requests.', 'artpulse' ),
				'type'  => 'text',
			),
		);
		foreach ( $location_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'location', $key, $cfg );
		}

		$email_fields = array(
			'email_method'       => array(
				'label'   => __( 'Email Method', 'artpulse' ),
				'type'    => 'select',
				'options' => array(
					'wp_mail'  => 'wp_mail',
					'mailgun'  => 'mailgun',
					'sendgrid' => 'sendgrid',
				),
			),
			'mailgun_api_key'    => array(
				'label' => __( 'Mailgun API Key', 'artpulse' ),
				'type'  => 'text',
			),
			'mailgun_domain'     => array(
				'label' => __( 'Mailgun Domain', 'artpulse' ),
				'type'  => 'text',
			),
			'sendgrid_api_key'   => array(
				'label' => __( 'SendGrid API Key', 'artpulse' ),
				'type'  => 'text',
			),
			'mailchimp_api_key'  => array(
				'label' => __( 'Mailchimp API Key', 'artpulse' ),
				'type'  => 'text',
			),
			'mailchimp_list_id'  => array(
				'label' => __( 'Mailchimp List ID', 'artpulse' ),
				'type'  => 'text',
			),
			'email_from_name'    => array(
				'label' => __( 'From Name', 'artpulse' ),
				'type'  => 'text',
			),
			'email_from_address' => array(
				'label' => __( 'From Address', 'artpulse' ),
				'type'  => 'text',
			),
		);
		foreach ( $email_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'emails', $key, $cfg );
		}

		$update_fields = array(
			'github_repo'         => array(
				'label' => __( 'GitHub Repo (owner/repo)', 'artpulse' ),
				'type'  => 'text',
			),
			'update_repo_url'     => array(
				'label' => __( 'Repository URL', 'artpulse' ),
				'desc'  => __( 'GitHub repository to pull updates from.', 'artpulse' ),
				'type'  => 'text',
			),
			'update_branch'       => array(
				'label' => __( 'Branch/Release', 'artpulse' ),
				'desc'  => __( 'Branch or release to track.', 'artpulse' ),
				'type'  => 'text',
			),
			'update_access_token' => array(
				'label' => __( 'Access Token', 'artpulse' ),
				'desc'  => __( 'Personal access token for private repos.', 'artpulse' ),
				'type'  => 'text',
			),
			'auto_update_enabled' => array(
				'label' => __( 'Auto-Update', 'artpulse' ),
				'desc'  => __( 'Check and apply updates daily.', 'artpulse' ),
				'type'  => 'checkbox',
			),
		);
		foreach ( $update_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'updates', $key, $cfg );
		}

		$monetization_fields = array(
			'stripe_pub_key'          => array(
				'label' => __( 'Stripe Publishable Key', 'artpulse' ),
				'type'  => 'text',
			),
			'stripe_secret_key'       => array(
				'label' => __( 'Stripe Secret Key', 'artpulse' ),
				'type'  => 'text',
			),
			'paypal_client_id'        => array(
				'label' => __( 'PayPal Client ID', 'artpulse' ),
				'type'  => 'text',
			),
			'paypal_secret'           => array(
				'label' => __( 'PayPal Secret', 'artpulse' ),
				'type'  => 'text',
			),
			'feature_price_event'     => array(
				'label' => __( 'Feature Event Price', 'artpulse' ),
				'type'  => 'number',
			),
			'feature_price_spotlight' => array(
				'label' => __( 'Feature Spotlight Price', 'artpulse' ),
				'type'  => 'number',
			),
		);
		foreach ( $monetization_fields as $key => $cfg ) {
			SettingsRegistry::register_field( 'monetization', $key, $cfg );
		}
	}
	public static function addMenu() {
		add_menu_page(
			__( 'ArtPulse', 'artpulse' ),
			__( 'ArtPulse', 'artpulse' ),
			'manage_options',
			'artpulse-settings',
			array( self::class, 'render' ),
			'dashicons-admin-generic',
			56
		);
		add_submenu_page(
			'artpulse-settings',
			__( 'Settings', 'artpulse' ),
			__( 'Settings', 'artpulse' ),
			'manage_options',
			'artpulse-settings',
			array( self::class, 'render' )
		);
		add_submenu_page(
			'artpulse-settings',
			__( 'Members', 'artpulse' ),
			__( 'Members', 'artpulse' ),
			'manage_options',
			'artpulse-members',
			array( self::class, 'renderMembersPage' )
		);
		add_submenu_page(
			'artpulse-settings',
			__( 'Engagement Dashboard', 'artpulse' ),
			__( 'Engagement', 'artpulse' ),
			'manage_options',
			'artpulse-engagement',
			array( EngagementDashboard::class, 'render' )
		);
		// Additional admin pages can hook into 'admin_menu' to add more submenus.
	}
	public static function enqueueAdminAssets( $hook ) {
		global $current_screen;
		if ( isset( $current_screen->id ) && $current_screen->id != 'toplevel_page_artpulse-settings' ) {
			return;
		}
		$chart_js_path              = 'assets/libs/chart.js/4.4.1/chart.min.js';
		$ap_admin_dashboard_path    = 'assets/js/ap-admin-dashboard.js';
		$ap_settings_tabs_path      = 'assets/js/ap-settings-tabs.js';
		$ap_update_diagnostics_path = 'assets/js/update-diagnostics.js';

		wp_enqueue_script(
			'chart-js',
			plugins_url( $chart_js_path, ARTPULSE_PLUGIN_FILE ),
			array(),
			filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $chart_js_path ),
			true
		);
		wp_enqueue_script(
			'ap-admin-dashboard',
			plugins_url( $ap_admin_dashboard_path, ARTPULSE_PLUGIN_FILE ),
			array( 'chart-js' ),
			filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $ap_admin_dashboard_path ),
			true
		);
		wp_enqueue_script(
			'ap-settings-tabs',
			plugins_url( $ap_settings_tabs_path, ARTPULSE_PLUGIN_FILE ),
			array(),
			filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $ap_settings_tabs_path ),
			true
		);
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'artpulse-settings' ) {
				wp_enqueue_script(
					'ap-update-diagnostics',
					plugins_url( $ap_update_diagnostics_path, ARTPULSE_PLUGIN_FILE ),
					array( 'wp-api-fetch' ),
					filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $ap_update_diagnostics_path ),
					true
				);
				wp_localize_script(
					'ap-update-diagnostics',
					'AP_UpdateData',
					array(
						'endpoint' => rest_url( 'artpulse/v1/update/diagnostics' ),
						'nonce'    => wp_create_nonce( 'wp_rest' ),
					)
				);
		}
		$signup_data = self::getMonthlySignupsByLevel();
		wp_localize_script( 'ap-admin-dashboard', 'APAdminStats', $signup_data );
	}
	public static function getMonthlySignupsByLevel() {
		global $wpdb;
		$levels = \ArtPulse\Core\MembershipManager::getLevels();
		$data   = array();
		$months = array();
		for ( $i = 5; $i >= 0; $i-- ) {
			$months[] = date_i18n( 'M', strtotime( "-{$i} months" ) );
		}
		foreach ( $levels as $level ) {
			$counts = array();
			for ( $i = 0; $i < 6; $i++ ) {
				$month     = date( 'Y-m-01', strtotime( "-{$i} months" ) );
				$nextMonth = date( 'Y-m-01', strtotime( '-' . ( $i - 1 ) . ' months' ) );
				$users     = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $wpdb->usermeta AS um
				         JOIN $wpdb->users AS u ON u.ID = um.user_id
				         WHERE um.meta_key = 'ap_membership_level'
				         AND um.meta_value = %s
				         AND u.user_registered >= %s AND u.user_registered < %s",
						$level,
						$month,
						$nextMonth
					)
				);
				$counts[]  = intval( $users );
			}
			$data[ $level ] = array_reverse( $counts ); // recent months last
		}
		$data['months'] = $months;
		return $data;
	}
	public static function trackLastLogin( $user_login, $user ) {
		update_user_meta( $user->ID, 'last_login', current_time( 'mysql' ) );
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if ( class_exists( '\\ArtPulse\\Admin\\LoginEventsPage' ) ) {
			\ArtPulse\Admin\LoginEventsPage::add_event( $user->ID, $ip );
		}
		if ( class_exists( '\\ArtPulse\\Core\\ActivityLogger' ) ) {
			ActivityLogger::log(
				intval( get_user_meta( $user->ID, 'ap_organization_id', true ) ),
				$user->ID,
				'login',
				'User logged in'
			);
		}
	}

	public static function trackLastLogout(): void {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			update_user_meta( $user_id, 'last_logout', current_time( 'mysql' ) );
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
			update_user_meta( $user_id, 'last_logout_ip', $ip );
			if ( class_exists( '\\ArtPulse\\Admin\\LoginEventsPage' ) && method_exists( '\\ArtPulse\\Admin\\LoginEventsPage', 'record_logout' ) ) {
				\ArtPulse\Admin\LoginEventsPage::record_logout( $user_id );
			}
			if ( class_exists( '\\ArtPulse\\Core\\ActivityLogger' ) ) {
				ActivityLogger::log(
					intval( get_user_meta( $user_id, 'ap_organization_id', true ) ),
					$user_id,
					'logout',
					'User logged out'
				);
			}
		}
	}

	public static function renderMembersPage() {
		$search_query = sanitize_text_field( $_GET['ap_search'] ?? '' );
		$level_filter = sanitize_text_field( $_GET['ap_level'] ?? '' );
		$level_filter = ucfirst( strtolower( $level_filter ) );
		$args         = array(
			'search'         => "*{$search_query}*",
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
			'orderby'        => 'registered',
			'order'          => 'DESC',
			'number'         => 100,
		);
		if ( ! empty( $level_filter ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'ap_membership_level',
					'value' => $level_filter,
				),
			);
		}
		$users = get_users( $args );
		// CSV Export
		if ( isset( $_GET['ap_export_csv'] ) ) {
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename="artpulse-members.csv"' );
			$output = fopen( 'php://output', 'w' );
			fputcsv( $output, array( 'Name', 'Email', 'Role', 'Membership Level', 'Submissions', 'Last Login', 'Registered At', 'Expiry' ) );
			foreach ( $users as $user ) {
				$level         = get_user_meta( $user->ID, 'ap_membership_level', true );
				$last_login    = get_user_meta( $user->ID, 'last_login', true );
				$expires       = get_user_meta( $user->ID, 'ap_membership_expires', true );
				$registered_at = get_user_meta( $user->ID, 'registered_at', true );
				fputcsv(
					$output,
					array(
						$user->display_name ?: $user->user_login,
						$user->user_email,
						implode( ', ', $user->roles ),
						$level ?: '—',
						count_user_posts( $user->ID, 'artwork' ), // change to match your CPT
						$last_login ?: '—',
						$registered_at ?: $user->user_registered,
						$expires ?: '—',
					)
				);
			}
			fclose( $output );
			exit;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ArtPulse Members', 'artpulse' ); ?></h1>
			<form method="get">
				<input type="hidden" name="page" value="artpulse-members" />
				<input type="text" name="ap_search" placeholder="<?php esc_attr_e( 'Search users...', 'artpulse' ); ?>" value="<?php echo esc_attr( $search_query ); ?>" />
				<select name="ap_level">
					<option value=""><?php esc_html_e( 'All Levels', 'artpulse' ); ?></option>
					<?php foreach ( \ArtPulse\Core\MembershipManager::getLevels() as $l ) : ?>
						<option value="<?php echo esc_attr( $l ); ?>" <?php selected( $level_filter, $l ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'artpulse' ); ?></button>
				<button type="submit" name="ap_export_csv" class="button-secondary"><?php esc_html_e( 'Export CSV', 'artpulse' ); ?></button>
			</form>
			<table class="widefat fixed striped">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Email', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Level', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Submissions', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Last Login', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Registered At', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Expires', 'artpulse' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'artpulse' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $users as $user ) :
					$level         = get_user_meta( $user->ID, 'ap_membership_level', true );
					$last_login    = get_user_meta( $user->ID, 'last_login', true );
					$expires       = get_user_meta( $user->ID, 'ap_membership_expires', true );
					$count         = count_user_posts( $user->ID, 'artwork' ); // change post type if needed
					$registered_at = get_user_meta( $user->ID, 'registered_at', true );
					?>
					<tr>
						<td><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></td>
						<td><?php echo esc_html( $user->user_email ); ?></td>
						<td><?php echo esc_html( $level ?: '—' ); ?></td>
						<td><?php echo esc_html( $count ); ?></td>
						<td><?php echo esc_html( $last_login ?: '—' ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $registered_at ?: $user->user_registered ) ) ); ?></td>
						<td><?php echo esc_html( $expires ?: '—' ); ?></td>
						<td>
							<a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>"><?php esc_html_e( 'View', 'artpulse' ); ?></a>
							|
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( "users.php?action=resetpassword&user={$user->ID}" ), 'reset_user_password_' . $user->ID ) ); ?>">
								<?php esc_html_e( 'Reset Password', 'artpulse' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if ( empty( $users ) ) : ?>
					<tr>
						<td colspan="8"><?php esc_html_e( 'No members found.', 'artpulse' ); ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public static function renderImportExportPage() {
		if ( isset( $_GET['ap_export_posts'] ) ) {
			$type    = sanitize_key( $_GET['ap_export_posts'] );
			$allowed = array( 'artpulse_org', 'artpulse_event', 'artpulse_artist', 'artpulse_artwork' );
			if ( in_array( $type, $allowed, true ) ) {
				ImportExportTab::exportPostsCsv( $type );
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import/Export', 'artpulse' ); ?></h1>
			<?php ImportExportTab::render(); ?>
		</div>
		<?php
	}
	public static function render() {
		if ( isset( $_POST['ap_test_webhook'] ) && check_admin_referer( 'ap_test_webhook_action' ) ) {
			$log   = get_option( 'artpulse_webhook_log', array() );
			$log[] = array(
				'type' => 'invoice.paid',
				'time' => current_time( 'mysql' ),
			);
			if ( count( $log ) > 20 ) {
				$log = array_slice( $log, -20 );
			}
			update_option( 'artpulse_webhook_log', $log );
			update_option( 'artpulse_webhook_status', 'Simulated' );
			update_option( 'artpulse_webhook_last_event', end( $log ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Webhook simulated successfully.', 'artpulse' ) . '</p></div>';
		}
		if ( isset( $_POST['ap_clear_webhook_log'] ) && check_admin_referer( 'ap_clear_webhook_log_action' ) ) {
			delete_option( 'artpulse_webhook_log' );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Webhook log cleared.', 'artpulse' ) . '</p></div>';
		}
		$webhook_status = get_option( 'artpulse_webhook_status', 'Unknown' );
		$last_event     = get_option( 'artpulse_webhook_last_event', array() );
		$log            = get_option( 'artpulse_webhook_log', array() );
		$tabs           = apply_filters( 'artpulse_settings_tabs', SettingsRegistry::get_tabs() );
		$tab_keys       = array_keys( $tabs );
		$current_tab    = sanitize_key( $_GET['tab'] ?? ( $tab_keys[0] ?? 'general' ) );

		// Fire a hook specific to the active tab so custom handlers can render
		// additional content or enqueue scripts.
		do_action( "artpulse_render_settings_tab_{$current_tab}" );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ArtPulse Settings', 'artpulse' ); ?></h1>
			<h2 class="nav-tab-wrapper" id="ap-settings-nav">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a href="#<?php echo esc_attr( $slug ); ?>" class="nav-tab<?php echo $current_tab === $slug ? ' nav-tab-active' : ''; ?>" data-tab="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<section id="<?php echo esc_attr( $slug === 'updates' ? 'updates' : 'ap-tab-' . $slug ); ?>" class="ap-settings-section" data-tab="<?php echo esc_attr( $slug ); ?>">
					<?php if ( $slug === 'import_export' ) : ?>
						<?php ImportExportTab::render(); ?>
					<?php elseif ( $slug === 'shortcodes' ) : ?>
						<?php \ArtPulse\Admin\ShortcodePages::render(); ?>
					<?php elseif ( $slug === 'config_backup' ) : ?>
						<?php ConfigBackupTab::render(); ?>
					<?php elseif ( $slug === 'updates' ) : ?>
						<form method="post" action="options.php">
							<?php WpAdminFns::settings_fields( 'artpulse_settings_group' ); ?>
							<?php WpAdminFns::do_settings_sections( 'artpulse-updates' ); ?>
							<?php WpAdminFns::submit_button(); ?>
						</form>
						<?php include ARTPULSE_PLUGIN_DIR . 'templates/admin/settings-tab-updates.php'; ?>
					<?php elseif ( $slug === 'widgets' ) : ?>
						<?php do_action( 'artpulse_render_settings_tab_widgets' ); ?>
					<?php elseif ( $slug === 'social_auto' ) : ?>
						<?php \ArtPulse\Integration\SocialAutoPoster::render_settings(); ?>
					<?php else : ?>
						<form method="post" action="options.php">
							<?php WpAdminFns::settings_fields( 'artpulse_settings_group' ); ?>
							<?php WpAdminFns::do_settings_sections( 'artpulse-' . $slug ); ?>
							<?php WpAdminFns::submit_button(); ?>
						</form>
						<?php if ( $slug === 'general' ) : ?>
							<hr>
							<h2 class="ap-card__title"><?php esc_html_e( 'System Status', 'artpulse' ); ?></h2>
							<p>
								<strong><?php esc_html_e( 'Webhook Status:', 'artpulse' ); ?></strong>
								<?php echo esc_html( $webhook_status ); ?><br>
								<strong><?php esc_html_e( 'Last Webhook Event:', 'artpulse' ); ?></strong>
								<?php echo esc_html( $last_event['type'] ?? 'None' ); ?><br>
								<strong><?php esc_html_e( 'Received At:', 'artpulse' ); ?></strong>
								<?php echo esc_html( $last_event['time'] ?? 'N/A' ); ?>
							</p>
							<h2 class="ap-card__title"><?php esc_html_e( 'Webhook Event Log', 'artpulse' ); ?></h2>
							<table class="widefat fixed striped">
								<thead>
								<tr>
									<th><?php esc_html_e( 'Timestamp', 'artpulse' ); ?></th>
									<th><?php esc_html_e( 'Event Type', 'artpulse' ); ?></th>
								</tr>
								</thead>
								<tbody>
								<?php
								if ( empty( $log ) ) {
									echo '<tr><td colspan="2">' . esc_html__( 'No webhook events logged.', 'artpulse' ) . '</td></tr>';
								} else {
									foreach ( array_reverse( $log ) as $entry ) {
										echo '<tr>';
										echo '<td>' . esc_html( $entry['time'] ) . '</td>';
										echo '<td>' . esc_html( $entry['type'] ) . '</td>';
										echo '</tr>';
									}
								}
								?>
								</tbody>
							</table>
							<form method="post">
								<?php \wp_nonce_field( 'ap_test_webhook_action' ); ?>
								<input type="submit" name="ap_test_webhook" class="button button-secondary" value="<?php esc_attr_e( 'Simulate Webhook Event', 'artpulse' ); ?>">
							</form>
							<form method="post">
								<?php \wp_nonce_field( 'ap_clear_webhook_log_action' ); ?>
								<input type="submit" name="ap_clear_webhook_log" class="button button-secondary" value="<?php esc_attr_e( 'Clear Webhook Log', 'artpulse' ); ?>">
							</form>
						<?php endif; ?>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
		<?php
	}
	public static function registerSettings() {
		if ( did_action( 'artpulse_register_settings_done' ) ) {
			return;
		}

		register_setting(
			'artpulse_settings_group',
			'artpulse_settings',
			array( 'sanitize_callback' => array( self::class, 'sanitizeSettings' ) )
		);

		do_action( 'artpulse_register_settings_done' );

		$tabs = apply_filters( 'artpulse_settings_tabs', SettingsRegistry::get_tabs() );
		foreach ( $tabs as $slug => $label ) {
			$section = 'ap_' . $slug . '_section';
			add_settings_section( $section, $label, '__return_false', 'artpulse-' . $slug );

			$fields = apply_filters( 'artpulse_settings_fields_' . $slug, SettingsRegistry::get_fields( $slug ) );
			foreach ( $fields as $key => $config ) {
				$config['key'] = $key;
				add_settings_field(
					$key,
					$config['label'],
					array( self::class, 'renderField' ),
					'artpulse-' . $slug,
					$section,
					array(
						'label_for'   => $key,
						'description' => $config['desc'] ?? '',
						'field'       => $config,
						'tab'         => $slug,
					)
				);
			}
		}
	}
	public static function sanitizeSettings( $input ) {
		// Load existing settings so values from other tabs are not lost
		$existing = get_option( 'artpulse_settings', array() );
		$output   = array();
		foreach ( $input as $key => $value ) {
			if ( in_array(
				$key,
				array(
					'stripe_enabled',
					'woocommerce_enabled',
					'debug_logging',
					'service_worker_enabled',
					'override_artist_membership',
					'override_org_membership',
					'override_member_membership',
					'auto_expire_events',
					'enable_artworks_for_sale',
					'disable_styles',
					'waitlists_enabled',
					'keep_data_on_uninstall',
					'enable_wp_admin_access',
					'oauth_google_enabled',
					'oauth_facebook_enabled',
					'oauth_apple_enabled',
					'enforce_two_factor',
					'auto_update_enabled',
				)
			) ) {
				$output[ $key ] = absint( $value );
			} elseif ( $key === 'payment_metrics_cache' || in_array( $key, array( 'default_rsvp_limit', 'min_rsvp_limit', 'max_rsvp_limit' ) ) ) {
				$output[ $key ] = absint( $value );
			} elseif ( $key === 'search_service' ) {
				$allowed        = array( 'algolia', 'elasticpress' );
				$output[ $key ] = in_array( $value, $allowed, true ) ? $value : '';
			} elseif ( $key === 'email_method' ) {
				$allowed        = array( 'wp_mail', 'mailgun', 'sendgrid' );
				$output[ $key ] = in_array( $value, $allowed, true ) ? $value : 'wp_mail';
			} elseif ( $key === 'email_from_address' ) {
				$output[ $key ] = sanitize_email( $value );
			} elseif ( in_array( $key, array( 'mailgun_api_key', 'mailgun_domain', 'sendgrid_api_key', 'mailchimp_api_key', 'mailchimp_list_id', 'email_from_name', 'openai_api_key' ), true ) ) {
				$output[ $key ] = sanitize_text_field( $value );
			} elseif ( $key === 'default_email_template' ) {
				$output[ $key ] = sanitize_textarea_field( $value );
			} else {
				$output[ $key ] = sanitize_text_field( $value );
			}
		}
		// Merge sanitized settings with the existing option so untouched keys remain
		return array_merge( $existing, $output );
	}
	public static function renderField( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return;
		}

		FieldRenderer::render( $args['field'], $args['tab'] ?? '' );
	}
}
