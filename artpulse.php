<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! defined( 'ARTPULSE_API_NAMESPACE' ) ) {
	define( 'ARTPULSE_API_NAMESPACE', 'artpulse/v1' );
}
/**
 * Plugin loader with version migration.
 */
// Core management bootstrap is loaded by artpulse-management.php
require_once __DIR__ . '/includes/db-schema.php';
require_once __DIR__ . '/includes/avatar-https-fix.php';
require_once __DIR__ . '/includes/rest-dedupe.php';
require_once __DIR__ . '/includes/rest-auth-code.php';
require_once __DIR__ . '/includes/install.php';
require_once __DIR__ . '/includes/migrations/2025_08_23_unify_webhook_logs.php';
require_once __DIR__ . '/ap-placeholder-bootstrap.php';
require_once __DIR__ . '/includes/reset-user-dashboard-meta.php';
require_once __DIR__ . '/includes/dashboard-debug-inspector.php';
require_once __DIR__ . '/includes/class-cli-dashboard-diagnose.php';
require_once __DIR__ . '/includes/class-cli-rest-route-audit.php';
require_once __DIR__ . '/includes/class-cli-widget-roles.php';
require_once __DIR__ . '/includes/class-cli-check-widget-presets.php';
require_once __DIR__ . '/includes/widget-logging.php';
require_once __DIR__ . '/includes/unhide-default-widgets.php';

// Register test-only REST route shim when in test mode.
$ap_test_mode = ( defined( 'AP_TEST_MODE' ) && AP_TEST_MODE ) || filter_var( getenv( 'AP_TEST_MODE' ), FILTER_VALIDATE_BOOLEAN );
if ( $ap_test_mode && class_exists( \ArtPulse\Rest\TestRouteShim::class ) ) {
        \ArtPulse\Rest\TestRouteShim::register();
}

// Load the textdomain after WordPress bootstrap but before most init callbacks.
add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'artpulse', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	},
	1
);

// NOTE: Avoid calling translation functions at file scope; translate within callbacks.

// Alias legacy widget IDs and bind real renderers after canonical registration.
add_action(
	'init',
	function () {
		\ArtPulse\Core\DashboardWidgetRegistry::alias( 'widget_widget_favorites', 'widget_favorites' );
		\ArtPulse\Core\DashboardWidgetRegistry::alias( 'widget_widget_near_me_events', 'widget_near_me_events' );
		\ArtPulse\Core\DashboardWidgetRegistry::alias( 'widget_news', 'widget_news_feed' );
		\ArtPulse\Core\DashboardWidgetRegistry::alias( 'widget_local-events', 'widget_local_events' );
		\ArtPulse\Core\DashboardWidgetRegistry::alias( 'widget_account-tools', 'widget_account_tools' );

		if ( class_exists( \ArtPulse\Widgets\FavoritesOverviewWidget::class ) ) {
			\ArtPulse\Core\DashboardWidgetRegistry::bindRenderer(
				'widget_my_favorites',
				array( \ArtPulse\Widgets\FavoritesOverviewWidget::class, 'render' )
			);
		}

		if ( class_exists( \ArtPulse\Widgets\NearbyEventsWidget::class ) ) {
			$method = method_exists( \ArtPulse\Widgets\NearbyEventsWidget::class, 'renderMap' ) ? 'renderMap' : 'render';
			\ArtPulse\Core\DashboardWidgetRegistry::bindRenderer(
				'widget_nearby_events_map',
				array( \ArtPulse\Widgets\NearbyEventsWidget::class, $method )
			);
		}
	},
	20
);

// Ensure role sync runs before audits access visibility options.
add_action( 'init', array( \ArtPulse\Core\WidgetRoleSync::class, 'sync' ), 20 );

// Normalize any saved widget ids to their canonical forms.
add_action(
	'admin_init',
	function () {
		$keys = array( 'artpulse_widget_roles', 'artpulse_hidden_widgets', 'artpulse_dashboard_layouts' );
		foreach ( $keys as $k ) {
			$v       = get_option( $k, array() );
			$changed = false;
			array_walk_recursive(
				$v,
				function ( &$id ) use ( &$changed ) {
					$canon = \ArtPulse\Support\WidgetIds::canonicalize( $id );
					if ( $canon !== $id ) {
						$id      = $canon;
						$changed = true; }
				}
			);
			if ( $changed ) {
				$v = is_array( $v ) ? array_map( fn( $arr )=>array_values( array_unique( $arr ) ), $v ) : $v;
				update_option( $k, $v, true );
			}
		}
	},
	20
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/includes/class-cli-debug-dashboard.php';
	\WP_CLI::add_command( 'artpulse debug-dashboard', \ArtPulse\CLI\DebugDashboardCommand::class );
	require_once __DIR__ . '/includes/class-cli-create-dashboard-page.php';
	\WP_CLI::add_command( 'artpulse create-user-dashboard-page', \ArtPulse\CLI\CreateUserDashboardPageCommand::class );
	require_once __DIR__ . '/src/Cli/WidgetDoctor.php';
	\WP_CLI::add_command( 'artpulse widgets', \ArtPulse\Cli\WidgetDoctor::class );
	require_once __DIR__ . '/src/Cli/WidgetAudit.php';
	( new \ArtPulse\Cli\WidgetAudit() )->register();
}

// Development helpers
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	$dev_file = __DIR__ . '/includes/dev/debug-rest.php';
	if ( file_exists( $dev_file ) ) {
		require_once $dev_file;
	}
}

// Migrate legacy widget visibility option to unified artpulse_widget_roles.
add_action(
	'plugins_loaded',
	function () {
		$legacy = get_option( 'ap_widget_visibility_settings', false );
		if ( $legacy !== false && \ArtPulse\Support\OptionUtils::get_array_option( 'artpulse_widget_roles' ) === array() ) {
			update_option( 'artpulse_widget_roles', $legacy );
			delete_option( 'ap_widget_visibility_settings' );
		}
	}
);

register_activation_hook(
	ARTPULSE_PLUGIN_FILE,
	function () {
		$settings = get_option( 'artpulse_settings', array() );
		$settings = array_merge( artpulse_get_default_settings(), $settings );
		update_option( 'artpulse_settings', $settings );
	}
);

// Create webhook log table on activation
register_activation_hook( ARTPULSE_PLUGIN_FILE, 'artpulse_create_webhook_logs_table' );
add_action( 'artpulse_upgrade', 'ap_unify_webhook_logs_migration', 10, 2 );

// Setup monetization tables on activation
register_activation_hook( ARTPULSE_PLUGIN_FILE, 'ArtPulse\\DB\\create_monetization_tables' );

// Optional manual repair: create tables via ?repair_artpulse_db
add_action(
	'init',
	function () {
		if ( current_user_can( 'administrator' ) && isset( $_GET['repair_artpulse_db'] ) ) {
			ArtPulse\DB\create_monetization_tables();
			esc_html_e( '✅ ArtPulse DB tables created.', 'artpulse' );
		}
	}
);


// Ensure tables stay up to date when plugin updates
add_action(
	'plugins_loaded',
	function () {
		$current = get_option( 'artpulse_db_version', '0.0.0' );
		if ( defined( 'ARTPULSE_VERSION' ) && version_compare( $current, ARTPULSE_VERSION, '<' ) ) {
			ArtPulse\DB\create_monetization_tables();
			update_option( 'artpulse_db_version', ARTPULSE_VERSION );
		}
	}
);

add_action(
	'plugins_loaded',
	function () {
		if ( defined( 'ARTPULSE_VERSION' ) && get_option( 'artpulse_version' ) !== ARTPULSE_VERSION ) {
			ArtPulse\DB\create_monetization_tables();
			update_option( 'artpulse_version', ARTPULSE_VERSION );
		}
	}
);

// One-time capability upgrader
add_action(
	'plugins_loaded',
	function () {
		if ( ! get_option( 'ap_caps_v2_applied' ) ) {
			require_once ARTPULSE_PLUGIN_DIR . 'src/Core/RoleSetup.php';
			ArtPulse\Core\RoleSetup::assign_capabilities();

			// Earlier migrations removed the dashboard capability from members,
			// which prevented the custom dashboard from loading. Restore it for
			// the role and any existing users to ensure dashboard access.
			if ( $role = get_role( 'member' ) ) {
				$role->add_cap( 'view_artpulse_dashboard' );
			}

			$members = get_users(
				array(
					'role'   => 'member',
					'fields' => array( 'ID' ),
				)
			);
			foreach ( $members as $u ) {
				$user = new WP_User( $u->ID );
				if ( ! $user->has_cap( 'view_artpulse_dashboard' ) ) {
					$user->add_cap( 'view_artpulse_dashboard' );
				}
			}

			update_option( 'ap_caps_v2_applied', 1 );
		}
	},
	20
);

// Register Diagnostics admin page
add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'ArtPulse Diagnostics', 'artpulse' ),
			__( 'AP Diagnostics', 'artpulse' ),
			'manage_options',
			'ap-diagnostics',
			'ap_diagnostics_page_loader',
			'dashicons-admin-tools',
			99
		);
	}
);

function ap_diagnostics_page_loader() {
	$path = plugin_dir_path( __FILE__ ) . 'admin/ap-diagnostics-page.php';
	if ( file_exists( $path ) ) {
		include $path;
	} else {
		echo '<div class="notice notice-error"><p>' .
			esc_html__( 'Diagnostics file not found.', 'artpulse' ) .
			'</p></div>';
	}
}

// AJAX handler for diagnostics test
add_action(
	'wp_ajax_ap_ajax_test',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_diagnostics_test', 'nonce' );

		wp_send_json_success(
			array(
				'message' => __( 'AJAX is working, nonce is valid, and you are authenticated.', 'artpulse' ),
			)
		);
	}
);

// Ensure administrator retains core page and post capabilities.
add_action(
	'init',
	function () {
		$admin = get_role( 'administrator' );
		if ( ! $admin ) {
			return;
		}
		$required = array(
			'edit_pages',
			'publish_pages',
			'edit_posts',
			'edit_others_pages',
			'edit_published_pages',
			'delete_pages',
			'delete_others_pages',
		);
		foreach ( $required as $cap ) {
			if ( ! $admin->has_cap( $cap ) ) {
				$admin->add_cap( $cap );
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					ap_log( sprintf( 'ap init restored %s capability for administrators', $cap ) );
				}
			}
		}
	},
	1
);

// Detect and log capability filters that may interfere with admin rights.
add_action(
	'init',
	function () {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && get_current_user_id() ) {
			foreach ( array( 'user_has_cap', 'map_meta_cap' ) as $hook ) {
				if ( has_filter( $hook ) ) {
					ap_log( sprintf( 'ArtPulse: filter detected on %s', $hook ) );
				}
			}
		}
	},
	100
);

// Final safeguard: never allow other filters to strip core caps from administrators.
add_filter(
	'user_has_cap',
	function ( array $allcaps, array $caps, array $args, \WP_User $user ): array {
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			$required = array(
				'edit_pages',
				'publish_pages',
				'edit_posts',
				'edit_others_pages',
				'edit_published_pages',
				'delete_pages',
				'delete_others_pages',
			);
			foreach ( $required as $cap ) {
				if ( empty( $allcaps[ $cap ] ) ) {
					$allcaps[ $cap ] = true;
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG && get_current_user_id() ) {
						ap_log( sprintf( 'ap user_has_cap restored %s for admin %d', $cap, $user->ID ) );
					}
				}
			}
		}
		return $allcaps;
	},
	PHP_INT_MAX,
	4
);
