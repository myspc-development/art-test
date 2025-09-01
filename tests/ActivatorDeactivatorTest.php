<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**

 * @group activatordeactivator

 */

class ActivatorDeactivatorTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( ! defined( 'ABSPATH' ) ) {
			$tmp = sys_get_temp_dir() . '/wp/';
			define( 'ABSPATH', $tmp );
			if ( ! is_dir( $tmp . 'wp-admin/includes' ) ) {
				mkdir( $tmp . 'wp-admin/includes', 0777, true );
			}
			if ( ! file_exists( $tmp . 'wp-admin/includes/upgrade.php' ) ) {
				file_put_contents( $tmp . 'wp-admin/includes/upgrade.php', '<?php' );
			}
		}

		Functions\when( '__' )->alias( fn( $t ) => $t );
		Functions\when( 'add_filter' )->justReturn( true );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_activate_creates_tables_roles_and_pages(): void {
		global $wpdb;
		$wpdb = new class() {
			public $prefix = 'wp_';
			public function get_charset_collate() {
				return 'utf8mb4'; }
		};

		$capturedSql = '';
		Functions\when( 'dbDelta' )->alias(
			function ( $sql ) use ( &$capturedSql ) {
				$capturedSql = $sql;
			}
		);

		$addedRoles = array();
		Functions\when( 'get_role' )->alias( fn() => null );
		Functions\when( 'add_role' )->alias(
			function ( $slug, $label, $caps ) use ( &$addedRoles ) {
				$addedRoles[ $slug ] = $caps;
				return true;
			}
		);

		$insertedPages = array();
		Functions\when( 'get_page_by_path' )->justReturn( false );
		Functions\when( 'wp_insert_post' )->alias(
			function ( $args ) use ( &$insertedPages ) {
				$insertedPages[] = $args['post_name'];
				return rand( 1, 100 );
			}
		);
		Functions\when( 'update_post_meta' )->justReturn( true );
		Functions\when( 'flush_rewrite_rules' )->justReturn( true );

		require_once __DIR__ . '/../includes/class-activator.php';

		ArtPulse_Activator::activate();

		$this->assertStringContainsString( 'CREATE TABLE', $capturedSql );
		$this->assertStringContainsString( 'wp_ap_placeholder', $capturedSql );

		$this->assertArrayHasKey( 'member', $addedRoles );
		$this->assertArrayHasKey( 'artist', $addedRoles );
		$this->assertArrayHasKey( 'organization', $addedRoles );

		foreach ( array( 'login', 'dashboard', 'events', 'artists', 'calendar' ) as $slug ) {
			$this->assertContains( $slug, $insertedPages );
		}
	}

	public function test_deactivate_flushes_rewrite_rules(): void {
		Functions\expect( 'flush_rewrite_rules' )->once()->andReturn( true );
		require_once __DIR__ . '/../includes/class-deactivator.php';
		ArtPulse_Deactivator::deactivate();
		$this->addToAssertionCount( 1 );
	}

	public function test_activation_callbacks_initialize_options_tables_and_capabilities(): void {
		// Capture activation and plugins_loaded callbacks.
		$activationCallbacks = array();
		Functions\when( 'register_activation_hook' )->alias(
			function ( $file, $callback ) use ( &$activationCallbacks ) {
				$activationCallbacks[] = $callback;
			}
		);

		$pluginLoadedCallbacks = array();
		Functions\when( 'add_action' )->alias(
			function ( $hook, $callback, $priority = null ) use ( &$pluginLoadedCallbacks ) {
				if ( $hook === 'plugins_loaded' ) {
					$pluginLoadedCallbacks[] = $callback;
				}
				return true;
			}
		);

		// Simulate WordPress option storage.
		$options = array();
		Functions\when( 'get_option' )->alias(
			function ( $name, $default = false ) use ( &$options ) {
				return $options[ $name ] ?? $default;
			}
		);
		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) use ( &$options ) {
				$options[ $name ] = $value;
				return true;
			}
		);
		Functions\when( 'delete_option' )->alias(
			function ( $name ) use ( &$options ) {
				unset( $options[ $name ] );
				return true;
			}
		);

		// Default settings used during activation.
		$defaults = array( 'theme' => 'default' );
		Functions\when( 'artpulse_get_default_settings' )->justReturn( $defaults );

                // Minimal stubs for plugin helpers.
                Functions\when( 'plugin_basename' )->alias( fn( $file ) => basename( $file ) );
		Functions\when( 'plugins_url' )->justReturn( '' );
		Functions\when( 'current_time' )->justReturn( 'now' );

		// Prepare role object and related stubs.
		$role = new class() {
			public array $caps = array();
			public function add_cap( $cap ) {
				$this->caps[ $cap ] = true; }
			public function has_cap( $cap ) {
				return ! empty( $this->caps[ $cap ] ); }
		};
		Functions\when( 'get_role' )->alias( fn( $name ) => $name === 'member' ? $role : null );
		Functions\when( 'get_users' )->justReturn( array() );

		if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
			define( 'ARTPULSE_PLUGIN_FILE', dirname( __DIR__ ) . '/artpulse.php' );
		}
		if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
			define( 'ARTPULSE_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
		}

		require dirname( __DIR__ ) . '/artpulse.php';

		// Stub monetization table creation after definitions loaded.
		$tablesCreated = false;
		Functions\when( 'ArtPulse\\DB\\create_monetization_tables' )->alias(
			function () use ( &$tablesCreated ) {
				$tablesCreated = true;
			}
		);
		Functions\when( 'artpulse_create_webhook_logs_table' )->alias( fn() => null );

		// Execute activation callbacks.
		foreach ( $activationCallbacks as $cb ) {
			if ( is_callable( $cb ) ) {
				$cb();
			} elseif ( is_string( $cb ) && function_exists( $cb ) ) {
				$cb();
			}
		}

		// Execute the plugins_loaded callback to assign capabilities.
		foreach ( $pluginLoadedCallbacks as $cb ) {
			$cb();
		}

		$this->assertSame( $defaults, $options['artpulse_settings'] ?? null );
		$this->assertTrue( $tablesCreated, 'Monetization tables should be created.' );
		$this->assertTrue( $role->has_cap( 'view_artpulse_dashboard' ) );

		// Cleanup
		delete_option( 'artpulse_settings' );
		delete_option( 'ap_caps_v2_applied' );
	}
}
