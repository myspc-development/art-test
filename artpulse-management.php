<?php
/**
 * Plugin Name:     ArtPulse Management
 * Description:     Management plugin for ArtPulse.
 * Version:         1.3.18
 * Author:          craig
 * Text Domain:     artpulse
 * License:         GPL2
 * Requires PHP:    8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

// Define ARTPULSE_PLUGIN_FILE constant before loading dependencies
if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
	define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
}
// Enable automatic widget placeholders by default
if ( ! defined( 'AP_ENABLE_WIDGET_PLACEHOLDERS' ) ) {
	define( 'AP_ENABLE_WIDGET_PLACEHOLDERS', true );
}

add_action(
	'init',
	function () {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			$lang_dir = plugin_dir_path( __FILE__ ) . 'languages';
			wp_set_script_translations( 'ap-payment-dashboard', 'artpulse', $lang_dir );
			wp_set_script_translations( 'ap-engagement-dashboard', 'artpulse', $lang_dir );
		}
	}
);

// Load main plugin logic
require_once plugin_dir_path( __FILE__ ) . 'artpulse.php';

use ArtPulse\Core\Plugin;
use ArtPulse\Core\WooCommerceIntegration;
use ArtPulse\Core\ArtworkWooSync;
use ArtPulse\Core\Activator;
use ArtPulse\Admin\EnqueueAssets;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetVisibilityManager;
use ArtPulse\Core\WidgetRoleSync;
use ArtPulse\Admin\FrontendDashboardWidget;

// Suppress deprecated notices if WP_DEBUG enabled
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	@ini_set( 'display_errors', '0' );
	@error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
}

// Ensure PHP 8.2 or newer
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
	if ( function_exists( 'deactivate_plugins' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
	wp_die(
		esc_html(
			sprintf( 'ArtPulse requires PHP 8.2 or higher. You are running %s.', PHP_VERSION )
		)
	);
}

// Load Composer autoloader
$autoload_path = __DIR__ . '/vendor/autoload.php';
if ( ! file_exists( $autoload_path ) ) {
	if ( is_admin() ) {
		add_action(
			'admin_notices',
			static function () {
				echo '<div class="notice notice-error"><p>' .
				esc_html( 'ArtPulse Management plugin is missing the Composer autoloader. Run `composer install` in the plugin directory and activate the plugin again.' ) .
				'</p></div>';
			}
		);
	}
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	deactivate_plugins( plugin_basename( __FILE__ ) );
	return;
}
require_once $autoload_path;
require_once __DIR__ . '/src/Widgets/bootstrap.php';

// Foundation setup classes and shortcodes
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-roles.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-assets.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers-page.php';

ArtPulse_Assets::init();

register_activation_hook( __FILE__, array( 'ArtPulse_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ArtPulse_Deactivator', 'deactivate' ) );

// Ensure donations schema exists
register_activation_hook(
	__FILE__,
	function () {
		\ArtPulse\Install\Schema::ensure();
		update_option( 'artpulse_db_version', '1.0.0' );
	}
);
add_action(
	'admin_init',
	function () {
		if ( ! get_option( 'artpulse_db_version' ) ) {
			\ArtPulse\Install\Schema::ensure();
			update_option( 'artpulse_db_version', '1.0.0' );
		}
	}
);

require_once __DIR__ . '/includes/widget-loader.php';
add_action(
        'init',
        static function () {
                \ArtPulse\DashboardWidgetRegistryLoader::load_all();
        }
);

// Setup automatic plugin updates from GitHub
require_once plugin_dir_path( __FILE__ ) . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
require_once __DIR__ . '/includes/update-checker.php';

Plugin::register();
WidgetVisibilityManager::register();
WidgetRoleSync::register();
FrontendDashboardWidget::register();
// Allow access to the default WordPress dashboard by removing the
// AdminAccessManager redirect.
remove_action( 'admin_init', array( \ArtPulse\Core\AdminAccessManager::class, 'maybe_redirect_admin' ) );

/**
 * Ensure core database tables exist on activation.
 */
function ap_create_all_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	$tables   = array();
	$tables[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ap_org_user_roles (
        org_id BIGINT NOT NULL,
        user_id BIGINT NOT NULL,
        role VARCHAR(100) NOT NULL,
        status VARCHAR(50) DEFAULT 'active',
        PRIMARY KEY (org_id, user_id),
        KEY user_id (user_id)
    ) $charset_collate;";

	$tables[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ap_roles (
        role_key varchar(191) NOT NULL,
        parent_role_key varchar(191) NULL,
        display_name varchar(191) NOT NULL,
        PRIMARY KEY  (role_key)
    ) $charset_collate;";

	$tables[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ap_feedback (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT NOT NULL,
        note TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	foreach ( $tables as $sql ) {
		dbDelta( $sql );
	}
}
register_activation_hook( __FILE__, 'ap_create_all_tables' );
// Load shared frontend helpers
require_once __DIR__ . '/src/Frontend/EventHelpers.php';
require_once __DIR__ . '/src/Frontend/ShareButtons.php';
require_once __DIR__ . '/src/Frontend/DonationHelpers.php';
require_once __DIR__ . '/includes/widgets/class-ap-widget.php';
require_once __DIR__ . '/includes/widgets/class-favorite-portfolio-widget.php';
require_once __DIR__ . '/includes/class-favorites.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Blocks/Version.php';
require_once __DIR__ . '/src/Util/EventFeed.php';
require_once __DIR__ . '/includes/dashboard-widgets.php';
require_once __DIR__ . '/includes/dashboard-builder-widgets.php';
require_once __DIR__ . '/includes/dashboard-menu.php';
require_once __DIR__ . '/includes/role-upgrade-handler.php';
require_once __DIR__ . '/includes/guide-dashboard-widgets.php';
require_once __DIR__ . '/includes/business-dashboard-widgets.php';
require_once __DIR__ . '/includes/dashboard-messages-widget.php';
require_once __DIR__ . '/includes/user-actions.php';
require_once __DIR__ . '/includes/user-dashboard-page.php';
register_activation_hook( __FILE__, 'ap_ensure_user_dashboard_page' );
// Force HTTPS for avatar URLs to avoid mixed content issues
require_once __DIR__ . '/includes/avatar-https-fix.php';
require_once __DIR__ . '/includes/chat-db.php';
require_once __DIR__ . '/includes/settings-register.php';
require_once __DIR__ . '/includes/help-doc-renderer.php';
require_once __DIR__ . '/includes/admin-dashboard-widget-controller.php';
require_once __DIR__ . '/admin/page-community-roles.php';
require_once __DIR__ . '/admin/page-roles-editor.php';
require_once __DIR__ . '/admin/page-widget-visibility.php';
require_once __DIR__ . '/admin/page-widget-audit.php';
require_once __DIR__ . '/admin/page-dashboard-widgets-inspector.php';
require_once __DIR__ . '/admin/page-widget-health.php';

require_once __DIR__ . '/follow-api.php';
require_once __DIR__ . '/seo-meta.php';
require_once __DIR__ . '/auto-tagger.php';
require_once __DIR__ . '/admin/page-artpulse-ai.php';
require_once __DIR__ . '/pwa-manifest.php';
require_once __DIR__ . '/shortcodes/artist-comments.php';
require_once __DIR__ . '/shortcodes/user-dashboard.php';
require_once __DIR__ . '/includes/class-artpulse-rest-controller.php';
// The DashboardConfigController class provides this route.
// require_once __DIR__ . '/api/dashboard-config.php';

add_action( 'rest_api_init', array( \ArtPulse\Rest\RestRoutes::class, 'register_all' ) );
require_once __DIR__ . '/admin-menu.php';
require_once __DIR__ . '/includes/http-hooks.php';
// Legacy dashboard implementation removed in favor of DashboardWidgetRegistry
// based widgets loaded via the manifest.
require_once __DIR__ . '/widgets/QAChecklistWidget.php';
require_once __DIR__ . '/widgets/EventsWidget.php';
require_once __DIR__ . '/widgets/DonationsWidget.php';
require_once __DIR__ . '/widgets/OrgAnalyticsWidget.php';
require_once plugin_dir_path( __FILE__ ) . 'src/Rest/EventChatController.php';
require_once __DIR__ . '/enqueue-react-widgets.php';
require_once __DIR__ . '/widgets/placeholder-stubs.php';
require_once __DIR__ . '/includes/hooks.php';
require_once __DIR__ . '/includes/registration-hooks.php';
require_once __DIR__ . '/includes/roles.php';
require_once __DIR__ . '/includes/profile-roles.php';
require_once __DIR__ . '/includes/cleanup-dashboard-layouts.php';
require_once __DIR__ . '/includes/repair-dashboard-layouts.php';
require_once __DIR__ . '/includes/delete-dashboard-layouts.php';
require_once __DIR__ . '/includes/member-dashboard-bootstrap.php';

add_action( 'artpulse_upgrade', 'ap_migrate_org_sub_roles', 10, 2 );

/**
 * Migrate deprecated organization sub-roles to the main role.
 *
 * @param string|null $old Previously installed version.
 * @param string|null $new Upgraded version.
 */
function ap_migrate_org_sub_roles( ?string $old = null, ?string $new = null ): void {
	$legacy_roles = array( 'org_manager', 'org_editor', 'org_viewer' );
	foreach ( $legacy_roles as $role ) {
		$users = get_users(
			array(
				'role'   => $role,
				'fields' => 'ID',
			)
		);
		foreach ( $users as $user_id ) {
			$user = new WP_User( $user_id );
			$user->remove_role( $role );
			if ( ! in_array( 'organization', (array) $user->roles, true ) ) {
				$user->add_role( 'organization' );
			}
		}
	}
}


// Ensure custom roles exist on every load
add_action(
	'init',
	function () {
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', 'Member', array( 'read' => true ) );
		}
		if ( ! get_role( 'artist' ) ) {
			add_role( 'artist', 'Artist', array( 'read' => true ) );
		}
		if ( ! get_role( 'organization' ) ) {
			add_role( 'organization', 'Organization', array( 'read' => true ) );
		}
	}
);

// Grant custom capabilities to administrators for dashboard access
add_action(
	'admin_init',
	function () {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( array( 'artist', 'member', 'organization' ) as $cap ) {
				if ( ! $admin->has_cap( $cap ) ) {
					$admin->add_cap( $cap );
				}
			}
		}
	}
);

// Handle user dashboard reset
add_action(
	'init',
	function () {
		if ( isset( $_POST['reset_user_layout'] ) && check_admin_referer( 'ap_reset_user_layout' ) ) {
			\ArtPulse\Core\DashboardController::reset_user_dashboard_layout( get_current_user_id() );
			wp_redirect( add_query_arg( 'layout_reset', '1', wp_get_referer() ) );
			exit;
		}

		if ( ! empty( $_POST['load_preset'] ) && check_admin_referer( 'ap_reset_user_layout' ) ) {
			$role   = sanitize_key( $_POST['preset_role'] ?? '' );
			$preset = sanitize_key( $_POST['load_preset'] );
			$layout = \ArtPulse\Core\DashboardController::load_preset_layout( $role, $preset );
			if ( $layout ) {
				update_user_meta( get_current_user_id(), 'ap_dashboard_layout', $layout );
				wp_redirect( add_query_arg( 'preset_loaded', '1', wp_get_referer() ) );
				exit;
			}
		}
	}
);


// Load developer sample widgets for demonstration purposes.
// See docs/developer/sample-widgets.md for details on these examples.

/**
 * Copy bundled Salient templates to the active child theme.
 */
function ap_copy_templates_to_child_theme() {
	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	$source_dir  = plugin_dir_path( __FILE__ ) . 'templates/salient/';
	$target_root = get_stylesheet_directory();
	$target_dir  = trailingslashit( $target_root ) . 'templates/salient/';

	if ( ! $wp_filesystem->is_writable( $target_root ) ) {
		ap_log( 'ArtPulse: child theme directory is not writable.' );
		add_action(
			'admin_notices',
			static function () {
				echo '<div class="notice notice-error"><p>' .
				esc_html__( 'ArtPulse templates could not be copied. Child theme directory is not writable.', 'artpulse' ) .
				'</p></div>';
			}
		);
		return;
	}

	if ( ! $wp_filesystem->is_dir( $target_dir ) ) {
		if ( ! $wp_filesystem->mkdir( $target_dir, FS_CHMOD_DIR ) ) {
			ap_log( 'ArtPulse: failed to create templates directory.' );
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' .
					esc_html__( 'ArtPulse templates directory could not be created.', 'artpulse' ) .
					'</p></div>';
				}
			);
			return;
		}
	}

	$files = array(
		'single-artpulse_event.php',
		'content-artpulse_event.php',
		'archive-artpulse_event.php',
		'single-artpulse_artist.php',
		'single-artist_profile.php',
	);

	foreach ( $files as $file ) {
		$source = $source_dir . $file;
		if ( ! $wp_filesystem->exists( $source ) ) {
			continue;
		}
		$destination = ( $file === 'single-artpulse_event.php' )
			? trailingslashit( $target_root ) . $file
			: $target_dir . $file;

		if ( ! $wp_filesystem->copy( $source, $destination, true, FS_CHMOD_FILE ) ) {
			ap_log( "ArtPulse: failed to copy template $file" );
			add_action(
				'admin_notices',
				static function () use ( $file ) {
					printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( sprintf( __( 'ArtPulse template %s could not be copied.', 'artpulse' ), $file ) ) );
				}
			);
		}
	}
}

// Instantiate WooCommerce integration (if needed for runtime)
$plugin      = new WooCommerceIntegration();
$artworkSync = new ArtworkWooSync();

function artpulse_activate() {
	\ArtPulse\Core\Activator::activate();
}

// ✅ Hook for activation
register_activation_hook(
	ARTPULSE_PLUGIN_FILE,
	function () {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		artpulse_create_custom_table();
		\ArtPulse\Core\FeedbackManager::install_table();
		artpulse_activate(); // WooCommerceIntegration has no activate() method
		if ( defined( 'ARTPULSE_SKIP_TEMPLATE_COPY' ) ? ! ARTPULSE_SKIP_TEMPLATE_COPY : false ) {
			ap_copy_templates_to_child_theme();
		}

		// Initialize default dashboard widget layout if missing
		if ( false === get_option( 'ap_dashboard_widget_config', false ) ) {
			$roles   = array_keys( wp_roles()->roles );
			$default = array();
			foreach ( $roles as $role ) {
				$ids              = \ArtPulse\Core\DashboardController::get_widgets_for_role( $role );
				$default[ $role ] = array_map(
					fn( $id ) => array(
						'id'      => $id,
						'visible' => true,
					),
					$ids
				);
			}
			add_option( 'ap_dashboard_widget_config', $default );
		}
	}
);

function ap_install_tables() {
	$installed = get_option( 'ap_db_version', '0.0.0' );
	if ( version_compare( $installed, '1.5.0', '<' ) ) {
		require_once __DIR__ . '/includes/db-schema.php';
		\ArtPulse\DB\create_monetization_tables();
		\ArtPulse\Core\MultiOrgRoles::maybe_install_table();

		global $wpdb;
		$table  = $wpdb->prefix . 'ap_org_user_roles';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			ap_log( "❌ Failed to create table $table" );
			add_action(
				'admin_notices',
				static function () use ( $table ) {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html(
							sprintf(
							/* translators: %s: table name */
								__( 'ArtPulse table %s could not be created. Check database permissions.', 'artpulse' ),
								$table
							)
						)
					);
				}
			);
		}

		update_option( 'ap_db_version', '1.5.0' );
	}
}
register_activation_hook( ARTPULSE_PLUGIN_FILE, 'ap_install_tables' );

/**
 * Verify core messaging tables exist on each request.
 */
function ap_verify_core_tables() {
	\ArtPulse\Community\DirectMessages::maybe_install_table();
	\ArtPulse\Community\NotificationManager::maybe_install_table();
	\ArtPulse\Monetization\PayoutManager::maybe_install_table();
	if ( class_exists( '\\ArtPulse\\Admin\\OrgCommunicationsCenter' ) ) {
		\ArtPulse\Admin\OrgCommunicationsCenter::maybe_install_table();
	}
}
add_action( 'init', 'ap_verify_core_tables' );







function ap_flush_github_cache() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_flush_github_cache' );
	delete_option( 'ap_update_remote_sha' );
	delete_option( 'ap_update_last_check' );
	delete_option( 'ap_update_available' );
	wp_safe_redirect( add_query_arg( 'cache_flushed', '1', admin_url( 'admin.php?page=ap-diagnostics' ) ) );
	exit;
}
add_action( 'admin_post_ap_flush_github_cache', 'ap_flush_github_cache' );

function ap_repair_tables_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_repair_tables' );
	ap_verify_core_tables();
	wp_safe_redirect( add_query_arg( 'repaired', '1', admin_url( 'admin.php?page=ap-diagnostics' ) ) );
	exit;
}
add_action( 'admin_post_ap_repair_tables', 'ap_repair_tables_action' );

function ap_ping_apis() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_ping_apis' );
	$results           = array();
	$resp              = wp_remote_get( 'https://api.github.com', array( 'timeout' => 5 ) );
	$results['github'] = is_wp_error( $resp ) ? $resp->get_error_message() : wp_remote_retrieve_response_code( $resp );
	$opts              = get_option( 'artpulse_settings', array() );
	if ( ! empty( $opts['stripe_secret'] ) && class_exists( '\\Stripe\\StripeClient' ) ) {
		try {
			$stripe = new \Stripe\StripeClient( $opts['stripe_secret'] );
			$stripe->charges->all( array( 'limit' => 1 ) );
			$results['stripe'] = 'OK';
		} catch ( \Exception $e ) {
			$results['stripe'] = $e->getMessage();
		}
	}
	if ( $discord = get_option( 'ap_discord_webhook_url' ) ) {
		$discordResp        = wp_remote_post(
			$discord,
			array(
				'body'    => array( 'content' => 'Ping' ),
				'timeout' => 5,
			)
		);
		$results['discord'] = is_wp_error( $discordResp ) ? $discordResp->get_error_message() : wp_remote_retrieve_response_code( $discordResp );
	}
	update_option( 'ap_api_ping_results', $results );
	wp_safe_redirect( add_query_arg( 'ping', '1', admin_url( 'admin.php?page=ap-diagnostics' ) ) );
	exit;
}
add_action( 'admin_post_ap_ping_apis', 'ap_ping_apis' );

function ap_export_diagnostic_report() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_export_diagnostic_report' );
	$data = array(
		'plugin_version'     => defined( 'ARTPULSE_VERSION' ) ? ARTPULSE_VERSION : '',
		'installed'          => get_option( 'artpulse_install_time', 'N/A' ),
		'php_version'        => phpversion(),
		'memory_limit'       => ini_get( 'memory_limit' ),
		'rest_enabled'       => rest_url() !== '',
		'https'              => is_ssl(),
		'update_available'   => (bool) get_option( 'ap_update_available' ),
		'stripe_configured'  => ! empty( get_option( 'artpulse_settings', array() )['stripe_enabled'] ),
		'discord_configured' => (bool) get_option( 'ap_discord_webhook_url' ),
	);
	header( 'Content-Type: application/json' );
	header( 'Content-Disposition: attachment; filename="ap-diagnostics.json"' );
	echo wp_json_encode( $data, JSON_PRETTY_PRINT );
	exit;
}
add_action( 'admin_post_ap_export_diagnostic_report', 'ap_export_diagnostic_report' );

use ArtPulse\Integration\PortfolioMigration;
use ArtPulse\Integration\PortfolioSync;
use ArtPulse\Core\PortfolioSyncLogger;

function ap_sync_all_portfolios() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_sync_all_portfolios' );
	$count = PortfolioSync::sync_all();
	PortfolioSyncLogger::log( 'sync', 'Admin bulk sync', array( 'count' => $count ), get_current_user_id() );
	ap_clear_portfolio_cache();
	wp_safe_redirect( add_query_arg( 'synced', $count, admin_url( 'admin.php?page=ap-portfolio-sync' ) ) );
	exit;
}
add_action( 'admin_post_ap_sync_all_portfolios', 'ap_sync_all_portfolios' );

function ap_migrate_portfolio_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_migrate_portfolio' );
	$count = PortfolioMigration::migrate( false );
	PortfolioSyncLogger::log( 'migration', 'Admin migration', array( 'count' => $count ), get_current_user_id() );
	ap_clear_portfolio_cache();
	wp_safe_redirect( add_query_arg( 'migrated', $count, admin_url( 'admin.php?page=ap-portfolio-sync' ) ) );
	exit;
}
add_action( 'admin_post_ap_migrate_portfolio', 'ap_migrate_portfolio_admin' );

function ap_save_portfolio_sync_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Unauthorized', 'artpulse' ) );
	}
	check_admin_referer( 'ap_save_portfolio_sync_settings' );
	$types = isset( $_POST['sync_types'] ) ? array_map( 'sanitize_text_field', (array) $_POST['sync_types'] ) : array();
	update_option( 'ap_portfolio_sync_types', $types );
	$map = isset( $_POST['cat_map'] ) ? array_map( 'sanitize_text_field', (array) $_POST['cat_map'] ) : array();
	update_option( 'ap_portfolio_category_map', $map );
	wp_safe_redirect( admin_url( 'admin.php?page=ap-portfolio-sync' ) );
	exit;
}
add_action( 'admin_post_ap_save_portfolio_sync_settings', 'ap_save_portfolio_sync_settings' );

// Handle template copy action
add_action(
	'admin_init',
	function () {
		if ( ! isset( $_POST['ap_copy_templates'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'artpulse_copy_templates' );

		ap_copy_templates_to_child_theme();

		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success is-dismissible"><p>' .
				esc_html__( 'Templates copied to child theme.', 'artpulse' ) .
				'</p></div>';
			}
		);
	}
);

// ✅ Hook for deactivation
// register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);

function artpulse_create_custom_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'artpulse_data';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        artist_name varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Enqueue global styles on the frontend.
 */
/**
 * Check if the current post content contains any ArtPulse shortcode.
 *
 * @return bool
 */
function ap_page_has_artpulse_shortcode() {
	if ( ! is_singular() ) {
		return false;
	}

	global $post;

	if ( ! $post || empty( $post->post_content ) ) {
		return false;
	}

	return strpos( $post->post_content, '[ap_' ) !== false;
}

/**
 * Check if the current page contains a specific shortcode.
 */
function ap_page_has_shortcode( string $tag ): bool {
	if ( ! is_singular() ) {
		return false;
	}
	global $post;
	if ( ! $post || empty( $post->post_content ) ) {
		return false;
	}
	return has_shortcode( $post->post_content, $tag );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		global $post;
		if ( $post && has_shortcode( $post->post_content, 'ap_favorite_portfolio' ) ) {
			wp_enqueue_style( 'ap-frontend', plugins_url( '/assets/css/frontend.css', __FILE__ ) );
		}
	}
);

/**
 * Get the active theme accent color.
 *
 * @return string Hex color string.
 */
function ap_get_accent_color() {
	return get_theme_mod( 'accent_color', '#0073aa' );
}

/**
 * Adjust a hex color brightness by the given percentage.
 *
 * @param string $hex      Base color in hex format.
 * @param float  $percent  Percentage to lighten/darken (-1 to 1).
 * @return string Adjusted hex color.
 */
function ap_adjust_color_brightness( $hex, $percent ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) .
				str_repeat( substr( $hex, 1, 1 ), 2 ) .
				str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	$r = max( 0, min( 255, (int) ( $r * ( 1 + $percent ) ) ) );
	$g = max( 0, min( 255, (int) ( $g * ( 1 + $percent ) ) ) );
	$b = max( 0, min( 255, (int) ( $b * ( 1 + $percent ) ) ) );

	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Determine if ArtPulse frontend styles are disabled.
 *
 * @return bool
 */
function ap_styles_disabled() {
	$settings = get_option( 'artpulse_settings', array() );
	return ! empty( $settings['disable_styles'] );
}

/**
 * Check if non-admin users can access wp-admin.
 */
function ap_wp_admin_access_enabled() {
	$settings = get_option( 'artpulse_settings', array() );
	return ! empty( $settings['enable_wp_admin_access'] );
}

/**
 * Enqueue the global UI styles on the frontend.
 *
 * By default the styles are only loaded when a page contains an
 * ArtPulse shortcode. Themes or page builders can bypass this detection by
 * filtering {@see 'ap_bypass_shortcode_detection'} and returning true.
 */
function ap_enqueue_global_styles() {
	if ( is_admin() ) {
		return;
	}

	$bypass = apply_filters( 'ap_bypass_shortcode_detection', false );

	if ( $bypass || ap_page_has_artpulse_shortcode() ) {
		$accent = ap_get_accent_color();
		$hover  = ap_adjust_color_brightness( $accent, -0.1 );
		wp_add_inline_style(
			'ap-complete-dashboard-style',
			":root { --ap-primary: {$accent}; --ap-primary-hover: {$hover}; }"
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ap_enqueue_global_styles' );

/**
 * Enqueue dashboard styles only when a page uses an ArtPulse shortcode.
 */
function ap_enqueue_dashboard_styles() {
	if ( ! ap_page_has_artpulse_shortcode() ) {
		return;
	}

	wp_enqueue_style(
		'ap-complete-dashboard-style',
		plugin_dir_url( __FILE__ ) . 'assets/css/ap-complete-dashboard-frontend.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/ap-complete-dashboard-frontend.css' )
	);
	$user_css = plugin_dir_path( __FILE__ ) . 'assets/css/ap-user-dashboard.css';
	if ( file_exists( $user_css ) ) {
		wp_enqueue_style(
			'ap-user-dashboard-style',
			plugin_dir_url( __FILE__ ) . 'assets/css/ap-user-dashboard.css',
			array( 'ap-complete-dashboard-style' ),
			filemtime( $user_css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ap_enqueue_dashboard_styles' );

// Load modern frontend UI styles for Salient/WPBakery integration
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'ap-frontend-styles',
			plugin_dir_url( __FILE__ ) . 'assets/css/ap-frontend-styles.css',
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/ap-frontend-styles.css' )
		);
	}
);

/**
 * Enqueue the base plugin stylesheet.
 */
function ap_enqueue_main_style() {
	$relative = 'dist/bundle.css';
	$css_path = plugin_dir_path( __FILE__ ) . $relative;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'artpulse-bundle',
			plugin_dir_url( __FILE__ ) . $relative,
			array(),
			filemtime( $css_path )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ap_enqueue_main_style' );
add_action( 'admin_enqueue_scripts', 'ap_enqueue_main_style' );
add_action( 'admin_enqueue_scripts', fn() => wp_enqueue_style( 'dashicons' ) );
add_action( 'wp_enqueue_scripts', fn() => wp_enqueue_style( 'dashicons' ) );

/**
 * Optionally enqueue styles for the admin area.
 *
 * @param string $hook Current admin page hook.
 */
function ap_enqueue_admin_styles( $hook ) {
	if ( strpos( $hook, 'artpulse' ) !== false ) {
		$css_file = plugin_dir_path( __FILE__ ) . 'assets/css/ap-style.css';
		wp_enqueue_style(
			'ap-admin-ui',
			plugin_dir_url( __FILE__ ) . 'assets/css/ap-style.css',
			array(),
			file_exists( $css_file ) ? filemtime( $css_file ) : null
		);
	}
}
add_action( 'admin_enqueue_scripts', 'ap_enqueue_admin_styles' );

// Enqueue SortableJS and layout script on dashboard pages
$sortable_rel = 'assets/libs/sortablejs/Sortable.min.js';
$role_rel     = 'assets/js/role-dashboard.js';

add_action(
        'admin_enqueue_scripts',
        function ( $hook ) use ( $sortable_rel, $role_rel ) {
                // Only load on the main WordPress dashboard. The custom dashboard
                // page handles its own assets to avoid duplicate rendering.
                if ( $hook === 'index.php' ) {
                        $sortable_path = plugin_dir_path( __FILE__ ) . $sortable_rel;
                        wp_enqueue_script(
                                'sortablejs',
                                plugin_dir_url( __FILE__ ) . $sortable_rel,
                                array(),
                                file_exists( $sortable_path ) ? (string) filemtime( $sortable_path ) : null,
                                true
                        );
                        $role_path = plugin_dir_path( __FILE__ ) . $role_rel;
                        wp_enqueue_script(
                                'role-dashboard',
                                plugin_dir_url( __FILE__ ) . $role_rel,
                                array( 'jquery', 'sortablejs' ),
                                file_exists( $role_path ) ? (string) filemtime( $role_path ) : null,
                                true
                        );
                        wp_localize_script(
                                'role-dashboard',
                                'ArtPulseDashboard',
                                array(
                                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                                        'nonce'    => wp_create_nonce( 'ap_dashboard_nonce' ),
                                )
                        );
                }
        }
);


add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( $hook === 'toplevel_page_artpulse_roles' ) {
			wp_enqueue_script(
				'artpulse-roles-editor',
				plugins_url( 'assets/js/roles-editor.js', __FILE__ ),
				array( 'wp-api-fetch', 'wp-element', 'wp-components' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/roles-editor.js' ),
				true
			);
			wp_localize_script(
				'artpulse-roles-editor',
				'ArtPulseRoles',
				array(
					'apiNonce' => wp_create_nonce( 'wp_rest' ),
					'restUrl'  => rest_url( 'artpulse/v1/roles' ),
				)
			);
		}
	}
);



// Enqueue the full SortableJS library on dashboard pages.
add_action(
        'wp_enqueue_scripts',
        function () use ( $sortable_rel ) {
                if ( is_page( 'dashboard' ) || is_page( 'organization-dashboard' ) ) {
                        $sortable_path = plugin_dir_path( __FILE__ ) . $sortable_rel;
                        wp_enqueue_script(
                                'sortablejs',
                                plugins_url( $sortable_rel, __FILE__ ),
                                array(),
                                file_exists( $sortable_path ) ? (string) filemtime( $sortable_path ) : null,
                                true
                        );

                        $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/organization-dashboard.js';
                        wp_enqueue_script(
                                'organization-dashboard',
                                plugins_url( 'assets/js/organization-dashboard.js', __FILE__ ),
                                array( 'sortablejs' ),
                                file_exists( $script_path ) ? (string) filemtime( $script_path ) : null,
                                true
                        );
                        wp_localize_script(
                                'organization-dashboard',
                                'APWidgetOrder',
                                array(
                                        'ajax_url'   => admin_url( 'admin-ajax.php' ),
                                        'nonce'      => wp_create_nonce( 'ap_widget_order' ),
                                        'identifier' => get_current_user_id(),
                                )
                        );
                }
        }
);

// Deprecated: use REST endpoint /artpulse/v1/favorites instead

function ap_user_has_favorited( $user_id, $post_id ) {
	$post_type = get_post_type( $post_id );
	if ( class_exists( '\\ArtPulse\\Community\\FavoritesManager' ) && $post_type ) {
		return \ArtPulse\Community\FavoritesManager::is_favorited( $user_id, $post_id, $post_type );
	}
	$meta_key = ( $post_type == 'artpulse_event' ) ? 'ap_favorite_events' : 'ap_favorite_artworks';
	$favs     = get_user_meta( $user_id, $meta_key, true ) ?: array();
	return in_array( $post_id, $favs );
}

function ap_render_favorite_portfolio( $atts = array() ) {
	if ( ! is_user_logged_in() ) {
		return '<p>' . esc_html__( 'Please log in to view your favorites.', 'artpulse' ) . '</p>';
	}

	$atts = shortcode_atts(
		array(
			'category' => '',
			'limit'    => 12,
			'sort'     => 'date',
			'page'     => 1,
		),
		$atts,
		'ap_favorite_portfolio'
	);

	$cat   = sanitize_text_field( $atts['category'] );
	$limit = max( 1, intval( $atts['limit'] ) );
	$sort  = sanitize_key( $atts['sort'] );
	$paged = max( 1, intval( $atts['page'] ) );

	$user_id = get_current_user_id();
	if ( class_exists( '\\ArtPulse\\Community\\FavoritesManager' ) ) {
		$favs         = \ArtPulse\Community\FavoritesManager::get_user_favorites( $user_id );
		$favorite_ids = array_map( fn( $f ) => $f->object_id, $favs );
	} else {
		$fav_events   = get_user_meta( $user_id, 'ap_favorite_events', true ) ?: array();
		$fav_artworks = get_user_meta( $user_id, 'ap_favorite_artworks', true ) ?: array();
		$favorite_ids = array_merge( $fav_events, $fav_artworks );
	}

	ob_start();
	if ( $favorite_ids ) {
		$args = array(
			'post_type'      => array( 'artpulse_event', 'artpulse_artwork' ),
			'post__in'       => $favorite_ids,
			'orderby'        => $sort === 'random' ? 'rand' : 'post__in',
			'posts_per_page' => $limit,
			'paged'          => $paged,
		);
		if ( $cat ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $cat,
				),
			);
		}
		$cache_key = 'fav_portfolio_' . md5( serialize( array( $user_id, $args ) ) );
		$fav_query = ap_cache_get(
			$cache_key,
			static function () use ( $args ) {
				return new WP_Query( $args );
			}
		);
		echo '<div class="ap-fav-portfolio row portfolio-items">';
		while ( $fav_query->have_posts() ) :
			$fav_query->the_post();
			echo '<div class="col span_4">';
			if ( get_post_type() === 'artpulse_event' ) {
				echo ap_get_event_card( get_the_ID() );
			} else {
				?>
				<div class="nectar-portfolio-item">
					<a href="<?php the_permalink(); ?>">
				<?php
						$gallery_ids = get_post_meta( get_the_ID(), '_ap_submission_images', true );
				if ( is_array( $gallery_ids ) && $gallery_ids ) {
					echo '<div class="event-gallery swiper"><div class="swiper-wrapper">';
					foreach ( $gallery_ids as $img_id ) {
						echo '<div class="swiper-slide">' . wp_get_attachment_image( $img_id, 'portfolio-thumb', false, array( 'loading' => 'lazy' ) ) . '</div>';
					}
					echo '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
				} else {
					the_post_thumbnail( 'portfolio-thumb', array( 'loading' => 'lazy' ) );
				}
				?>
						<h3><?php the_title(); ?></h3>
					</a>
					<div class="ap-event-actions">
						<?php echo \ArtPulse\Frontend\ap_render_favorite_button( get_the_ID(), get_post_type() ); ?>
						<span class="ap-fav-count"><?php echo intval( get_post_meta( get_the_ID(), 'ap_favorite_count', true ) ); ?></span>
					</div>
				</div>
				<?php
			}
			echo '</div>';
		endwhile;
		echo '</div>';
		$pagination = paginate_links(
			array(
				'total'   => $fav_query->max_num_pages,
				'current' => $paged,
				'type'    => 'list',
			)
		);
		if ( $pagination ) {
			echo '<nav class="ap-fav-pagination">' . $pagination . '</nav>';
		}
		wp_reset_postdata();
	} else {
		echo '<p>' . esc_html__( 'No favorites yet. Click the star on any event or artwork to add it to your favorites!', 'artpulse' ) . '</p>';
	}
	return ob_get_clean();
}
\ArtPulse\Core\ShortcodeRegistry::register( 'ap_favorite_portfolio', 'Favorite Portfolio', 'ap_render_favorite_portfolio' );

function ap_favorites_analytics_widget( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'type'       => 'summary',
			'user_id'    => 0,
			'admin_only' => false,
			'roles'      => '',
		),
		$atts,
		'ap_favorites_analytics'
	);

	$admin_only = filter_var( $atts['admin_only'], FILTER_VALIDATE_BOOLEAN );
	$roles      = array_filter( array_map( 'trim', explode( ',', $atts['roles'] ) ) );
	$user_id    = intval( $atts['user_id'] );

	if ( $admin_only && ! current_user_can( 'manage_options' ) ) {
		return '';
	}
	if ( $roles ) {
		$user = wp_get_current_user();
		if ( ! array_intersect( $user->roles, $roles ) ) {
			return '';
		}
	}

	wp_enqueue_script( 'chart-js', plugins_url( 'assets/libs/chart.js/4.4.1/chart.min.js', ARTPULSE_PLUGIN_FILE ), array(), null );

	$limit = $atts['type'] === 'detailed' ? 20 : 5;

	ob_start();
	$args = array(
		'post_type'      => array( 'artpulse_event', 'artpulse_artwork' ),
		'meta_key'       => 'ap_favorite_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'posts_per_page' => $limit,
	);
	if ( $user_id ) {
		$args['author'] = $user_id;
	}
	$query = new WP_Query( $args );
	echo '<h4>Top Favorited Events/Artworks</h4><ul class="ap-analytics-widget">';
	while ( $query->have_posts() ) :
		$query->the_post();
		$trend  = get_post_meta( get_the_ID(), 'ap_favorite_trend', true ) ?: array();
		$labels = array();
		$counts = array();
		foreach ( array_slice( array_reverse( array_keys( $trend ) ), 0, 7 ) as $d ) {
			$labels[] = $d;
			$counts[] = $trend[ $d ];
		}
		?>
		<li>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<span><?php echo intval( get_post_meta( get_the_ID(), 'ap_favorite_count', true ) ); ?> <?php echo esc_html__( 'favorites', 'artpulse' ); ?></span>
			<canvas id="favTrendChart-<?php the_ID(); ?>" width="300" height="80"></canvas>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				var ctx = document.getElementById('favTrendChart-<?php the_ID(); ?>').getContext('2d');
				new Chart(ctx, {
					type: 'line',
					data: {
						labels: <?php echo json_encode( $labels ); ?>,
						datasets: [{
							label: 'Favorites per day',
							data: <?php echo json_encode( $counts ); ?>,
							borderColor: '#f5ab35',
							backgroundColor: 'rgba(245,171,53,0.1)',
							fill: true,
							tension: 0.3
						}]
					},
					options: {
						plugins: { legend: { display: false } },
						scales: {
							x: { display: true, title: { display: true, text: 'Date' } },
							y: { beginAtZero: true, title: { display: true, text: 'Favorites' } }
						}
					}
				});
			});
			</script>
		</li>
		<?php
	endwhile;
	echo '</ul>';
	wp_reset_postdata();
	return ob_get_clean();
}
\ArtPulse\Core\ShortcodeRegistry::register( 'ap_favorites_analytics', 'Favorites Analytics', 'ap_favorites_analytics_widget' );

function ap_enqueue_event_calendar_assets() {
	if ( is_page( 'events' ) || is_singular( 'artpulse_event' ) ) {
		wp_enqueue_style(
			'fullcalendar-css',
			plugins_url( 'assets/libs/fullcalendar/6.1.11/main.min.css', __FILE__ )
		);
		wp_enqueue_script(
			'fullcalendar-js',
			plugins_url( 'assets/libs/fullcalendar/6.1.11/main.min.js', __FILE__ ),
			array(),
			null,
			true
		);
		wp_enqueue_script( 'ap-event-calendar', plugin_dir_url( __FILE__ ) . 'assets/js/ap-event-calendar.js', array( 'fullcalendar-js', 'jquery' ), '1.0', true );
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'ap-event-calendar', 'type', 'module' );
		}
		wp_localize_script(
			'ap-event-calendar',
			'APCalendar',
			array(
				'events' => ap_get_events_for_calendar(),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ap_enqueue_event_calendar_assets' );

function ap_get_events_for_calendar() {
	$lat = isset( $_GET['lat'] ) ? floatval( $_GET['lat'] ) : null;
	$lng = isset( $_GET['lng'] ) ? floatval( $_GET['lng'] ) : null;

	return \ArtPulse\Util\ap_fetch_calendar_events( $lat, $lng );
}

function ap_get_event_card( int $event_id ): string {
	$path = locate_template( 'templates/event-card.php' );
	if ( ! $path ) {
		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/event-card.php';
	}
	if ( ! file_exists( $path ) ) {
		return '';
	}
	ob_start();
	include $path;
	return ob_get_clean();
}

function ap_get_collection_card( int $collection_id ): string {
	$path = locate_template( 'templates/collection-card.php' );
	if ( ! $path ) {
		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/collection-card.php';
	}
	if ( ! file_exists( $path ) ) {
		return '';
	}
	ob_start();
	include $path;
	return ob_get_clean();
}

function ap_get_events_for_map() {
	$query  = new WP_Query(
		array(
			'post_type'      => 'artpulse_event',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'meta_query'     => array(
				array(
					'key'     => 'event_lat',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'event_lng',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	$events = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$lat = get_post_meta( get_the_ID(), 'event_lat', true );
		$lng = get_post_meta( get_the_ID(), 'event_lng', true );
		if ( $lat === '' || $lng === '' ) {
			continue;
		}
		$events[] = array(
			'id'    => get_the_ID(),
			'title' => get_the_title(),
			'lat'   => (float) $lat,
			'lng'   => (float) $lng,
			'url'   => get_permalink(),
		);
	}
	wp_reset_postdata();
	return $events;
}

// === UI Toggle Demo ===
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers-ui.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/post-status-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/artist-meta-box.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/webhook-functions.php';

add_action(
	'wp_enqueue_scripts',
	function () {
		$ui_mode = ap_get_ui_mode();

		if ( $ui_mode === 'react' ) {
			wp_enqueue_script( 'ap-app-dashboard', plugin_dir_url( __FILE__ ) . 'dist/app-dashboard.js', array(), null, true );
		}
	}
);

\ArtPulse\Core\ShortcodeRegistry::register( 'ap_render_ui', 'Render UI', array( \ArtPulse\Core\DashboardController::class, 'render' ) );



add_action(
	'wp_footer',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div><strong>UI Mode:</strong>
        <a href="?ui_mode=tailwind">Tailwind</a> |
        <a href="?ui_mode=react">React</a></div>';
	}
);


// Force plugin template for single artpulse_event posts
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_singular( 'artpulse_event' ) ) {
			$custom_template = plugin_dir_path( __FILE__ ) . 'templates/salient/single-artpulse_event.php';
			if ( file_exists( $custom_template ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					ap_log( '✅ Plugin forcing use of single-artpulse_event.php' );
				}
				return $custom_template;
			}
		}
		return $template;
	},
	999
);

// Force plugin template for single artpulse_artist posts
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_singular( 'artpulse_artist' ) ) {
			$custom_template = plugin_dir_path( __FILE__ ) . 'templates/single-artpulse_artist.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	},
	998
);

// Force plugin template for single artist_profile posts
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_singular( 'artist_profile' ) ) {
			$custom_template = plugin_dir_path( __FILE__ ) . 'templates/single-artist_profile.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	},
	997
);

// Toggle Salient templates for portfolio posts
add_filter(
	'template_include',
	function ( $template ) {
		if ( is_singular( 'portfolio' ) && ap_get_portfolio_display_mode() === 'salient' ) {
			$custom = plugin_dir_path( __FILE__ ) . 'templates/salient/content-portfolio.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}
		if ( is_post_type_archive( 'portfolio' ) && ap_get_portfolio_display_mode() === 'salient' ) {
			$archive = plugin_dir_path( __FILE__ ) . 'templates/salient/content-portfolio-archive.php';
			if ( file_exists( $archive ) ) {
				return $archive;
			}
		}
		return $template;
	},
	997
);

// === React Form Demo ===
function artpulse_enqueue_react_form() {
	if ( ! ap_page_has_shortcode( 'react_form' ) ) {
		return;
	}
	wp_enqueue_script(
		'artpulse-react-form',
		plugin_dir_url( __FILE__ ) . 'dist/react-form.js',
		array( 'wp-element' ),
		'1.0.0',
		true
	);
	wp_localize_script(
		'artpulse-react-form',
		'apReactForm',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ap_react_form' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'artpulse_enqueue_react_form' );

function artpulse_render_react_form( $atts = array() ) {
	$atts = shortcode_atts( array( 'type' => 'default' ), $atts, 'react_form' );
	$type = sanitize_key( $atts['type'] );
	return '<div id="react-form-root" data-type="' . esc_attr( $type ) . '"></div>';
}
\ArtPulse\Core\ShortcodeRegistry::register( 'react_form', 'React Form', 'artpulse_render_react_form' );

function artpulse_handle_react_form() {
	check_ajax_referer( 'ap_react_form' );

	$name  = sanitize_text_field( $_POST['name'] ?? '' );
	$email = sanitize_email( $_POST['email'] ?? '' );

	if ( $name === '' || ! is_email( $email ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please provide a name and valid email.', 'artpulse' ),
			)
		);
	}

	global $wpdb;
	$table = $wpdb->prefix . 'ap_feedback';
	$wpdb->insert(
		$table,
		array(
			'user_id'     => get_current_user_id() ?: null,
			'type'        => 'react_form',
			'description' => sprintf( 'Name: %s', $name ),
			'email'       => $email,
			'tags'        => '',
			'context'     => '',
			'created_at'  => current_time( 'mysql' ),
		)
	);

	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( __( 'React form submission from %s', 'artpulse' ), $name );
	$message     = sprintf( "Name: %s\nEmail: %s", $name, $email );
	\ArtPulse\Core\EmailService::send( $admin_email, $subject, $message );

	wp_send_json_success(
		array(
			'message' => __( 'Form submitted successfully!', 'artpulse' ),
		)
	);
}
add_action( 'wp_ajax_submit_react_form', 'artpulse_handle_react_form' );
add_action( 'wp_ajax_nopriv_submit_react_form', 'artpulse_handle_react_form' );

// Dashboard preset loader via AJAX
add_action(
	'wp_ajax_ap_apply_preset',
	function () {
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_dashboard_nonce' );

		$user_id = get_current_user_id();
		$key     = sanitize_text_field( $_POST['preset_key'] ?? '' );
		$presets = \ArtPulse\Core\DashboardController::get_default_presets();

		if ( ! isset( $presets[ $key ] ) ) {
			wp_send_json_error( 'Invalid preset.' );
		}

		update_user_meta( $user_id, 'ap_dashboard_layout', $presets[ $key ]['layout'] );
		wp_send_json_success( array( 'message' => 'Preset applied.' ) );
	}
);

// Dashboard layout reset via AJAX
add_action(
	'wp_ajax_ap_reset_layout',
	function () {
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_dashboard_nonce' );

		$user_id = get_current_user_id();
		delete_user_meta( $user_id, 'ap_dashboard_layout' );

		wp_send_json_success( array( 'message' => 'Layout reset.' ) );
	}
);

if ( ! function_exists( 'ap_register_widget_once' ) ) {
	function ap_register_widget_once( $class, $id_base = null ) {
		global $wp_widget_factory;
		$id_base = $id_base ?: ( is_string( $class ) ? $class : null );
		if ( $id_base && empty( $wp_widget_factory->widgets[ $id_base ] ) ) {
			register_widget( $class );
		}
	}
}

add_action(
	'widgets_init',
	function () {
		ap_register_widget_once( 'AP_Widget', 'ap_shortcode_widget' );
		if ( class_exists( 'AP_Favorite_Portfolio_Widget' ) ) {
			ap_register_widget_once( 'AP_Favorite_Portfolio_Widget', 'ap_favorite_portfolio_widget' );
		}
	},
	1
);

// --- Help Guide Shortcodes & Styles ---
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		wp_enqueue_style(
			'ap-help-style',
			plugin_dir_url( __FILE__ ) . 'assets/css/ap-help.css',
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/ap-help.css' )
		);
	}
);

\ArtPulse\Core\ShortcodeRegistry::register(
	'ap_admin_guide',
	'Admin Guide',
	function () {
		return ap_render_help_markdown( 'Admin_Help.md' );
	}
);

\ArtPulse\Core\ShortcodeRegistry::register(
	'ap_member_guide',
	'Member Guide',
	function () {
		return ap_render_help_markdown( 'Member_Help.md' );
	}
);

add_action(
	'admin_bar_menu',
	function ( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( defined( 'AP_DEBUG' ) && ! AP_DEBUG ) {
			return;
		}
		$roles = array( 'member', 'artist', 'organization' );
		foreach ( $roles as $role ) {
			$wp_admin_bar->add_node(
				array(
					'id'    => 'ap-switch-' . $role,
					'title' => 'View as: ' . $role,
					'href'  => add_query_arg( 'ap_preview_role', $role, home_url( '/dashboard' ) ),
				)
			);
		}
	},
	100
);


