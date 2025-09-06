<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use ArtPulse\Admin\EnqueueAssets;
use function Patchwork\redefine;
use function Patchwork\restore;

/**

 * @group PHPUNIT
 */

final class EnqueueAssetsTest extends TestCase {

	private array $enqueuedScripts   = array();
	private array $enqueuedStyles    = array();
	private array $registeredScripts = array();
	private array $fs                = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
			define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
		}

		// Paths
		Functions\when( 'plugin_dir_path' )->alias( fn( $f ) => '/p/' );
				Functions\when( 'plugin_dir_url' )->alias( fn( $f ) => 'https://example.test/p/' );

				// File system
				Functions\when( 'file_exists' )->alias( fn( string $p ) => $this->fs[ $p ] ?? false );
				Functions\when( 'filemtime' )->alias( fn( string $p ) => 1234567890 );

				Functions\when( 'sanitize_key' )->alias( fn( $k ) => strtolower( preg_replace( '/[^a-z0-9_]/', '', $k ) ) );
				Functions\when( 'wp_unslash' )->alias( fn( $v ) => $v );

		// Script/style helpers
		Functions\when( 'wp_register_script' )->alias(
			function ( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
				$this->registeredScripts[ $handle ] = array(
					'handle'    => $handle,
					'src'       => $src,
					'deps'      => $deps,
					'ver'       => $ver,
					'in_footer' => $in_footer,
				);
			}
		);
		Functions\when( 'wp_enqueue_script' )->alias(
			function ( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
				if ( $src === '' && isset( $this->registeredScripts[ $handle ] ) ) {
					$r         = $this->registeredScripts[ $handle ];
					$src       = $r['src'];
					$deps      = $r['deps'];
					$ver       = $r['ver'];
					$in_footer = $r['in_footer'];
				}
				$this->enqueuedScripts[ $handle ] = array(
					'handle'    => $handle,
					'src'       => $src,
					'deps'      => $deps,
					'ver'       => $ver,
					'in_footer' => $in_footer,
				);
			}
		);
		Functions\when( 'wp_script_is' )->alias(
			function ( $handle, $list = 'enqueued' ) {
				if ( $list === 'registered' ) {
					return isset( $this->registeredScripts[ $handle ] );
				}
				if ( $list === 'enqueued' ) {
					return isset( $this->enqueuedScripts[ $handle ] );
				}
				return false;
			}
		);
		Functions\when( 'wp_enqueue_style' )->alias(
			function ( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
				$this->enqueuedStyles[ $handle ] = array(
					'handle' => $handle,
					'src'    => $src,
					'deps'   => $deps,
					'ver'    => $ver,
					'media'  => $media,
				);
			}
		);
		Functions\when( 'wp_style_is' )->alias(
			function ( $handle, $list = 'enqueued' ) {
				return $list === 'enqueued' ? isset( $this->enqueuedStyles[ $handle ] ) : false;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function touch( string $rel ): void {
		$this->fs[ '/p/' . ltrim( $rel, '/' ) ] = true;
	}

	private function script( string $handle ): ?array {
		return $this->enqueuedScripts[ $handle ] ?? null;
	}

	private function style( string $handle ): ?array {
		return $this->enqueuedStyles[ $handle ] ?? null;
	}

	public function test_register_wires_hooks(): void {
				Actions\expectAdded( 'enqueue_block_editor_assets' )->twice();
				Actions\expectAdded( 'admin_enqueue_scripts' )->once();
				Actions\expectAdded( 'wp' )->once();
				EnqueueAssets::register();
				$this->assertTrue( true );
	}

	public function test_maybe_enqueue_frontend_adds_hook_when_shortcode_present(): void {
			$handle = redefine( '\\ArtPulse\\Helpers\\GlobalHelpers::pageHasArtpulseShortcode', fn() => true );
			Actions\expectAdded( 'wp_enqueue_scripts' )->once();
			EnqueueAssets::maybe_enqueue_frontend();
			restore( $handle );
	}

	public function test_maybe_enqueue_frontend_skips_when_no_shortcode(): void {
			$handle = redefine( '\\ArtPulse\\Helpers\\GlobalHelpers::pageHasArtpulseShortcode', fn() => false );
			Actions\expectAdded( 'wp_enqueue_scripts' )->never();
			EnqueueAssets::maybe_enqueue_frontend();
			restore( $handle );
	}

	public function test_dashboard_admin_enqueues_with_sortable(): void {
		$this->touch( 'assets/css/dashboard.css' );
		$this->touch( 'assets/js/dashboard-role-tabs.js' );
		$this->touch( 'assets/js/role-dashboard.js' );
		$this->touch( 'assets/libs/sortablejs/Sortable.min.js' );

		EnqueueAssets::enqueue_admin( 'toplevel_page_ap-dashboard' );

		$this->assertNotNull( $this->style( 'ap-dashboard' ) );
		$this->assertNotNull( $this->script( 'ap-role-tabs' ) );
		$this->assertNotNull( $this->script( 'sortablejs' ) );
		$role = $this->script( 'role-dashboard' );
		$this->assertNotNull( $role );
		$this->assertContains( 'ap-role-tabs', $role['deps'] );
		$this->assertContains( 'sortablejs', $role['deps'] );
	}

	public function test_dashboard_admin_enqueues_without_sortable(): void {
		$this->touch( 'assets/css/dashboard.css' );
		$this->touch( 'assets/js/dashboard-role-tabs.js' );
		$this->touch( 'assets/js/role-dashboard.js' );

		EnqueueAssets::enqueue_admin( 'toplevel_page_ap-dashboard' );

		$role = $this->script( 'role-dashboard' );
		$this->assertNotNull( $role );
		$this->assertContains( 'ap-role-tabs', $role['deps'] );
		$this->assertNotContains( 'sortablejs', $role['deps'] );
	}

	public function test_chart_js_registered_in_admin(): void {
		$this->touch( 'assets/libs/chart.js/4.4.1/chart.min.js' );
		$this->touch( 'assets/js/ap-user-dashboard.js' );

		EnqueueAssets::enqueue_admin( 'toplevel_page_artpulse-settings' );

		$this->assertArrayHasKey( 'chart-js', $this->registeredScripts );
		$this->assertSame( 1234567890, $this->registeredScripts['chart-js']['ver'] );
		$dash = $this->script( 'ap-user-dashboard-js' );
		$this->assertNotNull( $dash );
		$this->assertContains( 'chart-js', $dash['deps'] );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );
	}

	public function test_block_editor_styles_enqueue(): void {
		$screen = new class() {
			public function is_block_editor(): bool {
				return true; }
		};
		Functions\when( 'get_current_screen' )->justReturn( $screen );
		$this->touch( 'assets/css/editor-styles.css' );

		EnqueueAssets::enqueue_block_editor_styles();

		$this->assertNotNull( $this->style( 'artpulse-editor-styles' ) );
	}

	public function test_import_export_tab_enqueues(): void {
		$this->touch( 'assets/libs/papaparse/papaparse.min.js' );
		$this->touch( 'assets/js/ap-csv-import.js' );

		$_GET['tab'] = 'import_export';

		EnqueueAssets::enqueue_admin( 'toplevel_page_artpulse-settings' );

		$this->assertNotNull( $this->script( 'papaparse' ) );
		$csv = $this->script( 'ap-csv-import' );
		$this->assertNotNull( $csv );
		$this->assertContains( 'papaparse', $csv['deps'] );
		$this->assertContains( 'wp-api-fetch', $csv['deps'] );

		unset( $_GET['tab'] );
	}

	public function test_block_editor_styles_not_enqueued_when_file_missing(): void {
		$screen = new class() {
			public function is_block_editor(): bool {
				return true; }
		};
		Functions\when( 'get_current_screen' )->justReturn( $screen );

		EnqueueAssets::enqueue_block_editor_styles();

		$this->assertNull( $this->style( 'artpulse-editor-styles' ) );
	}

	public function test_settings_page_enqueues_scripts(): void {
		$this->touch( 'assets/js/ap-analytics.js' );
		$this->touch( 'assets/js/ap-user-dashboard.js' );
		$this->touch( 'assets/libs/chart.js/4.4.1/chart.min.js' );

		EnqueueAssets::enqueue_admin( 'toplevel_page_artpulse-settings' );

		$this->assertNotNull( $this->script( 'ap-analytics' ) );
		$dash = $this->script( 'ap-user-dashboard-js' );
		$this->assertNotNull( $dash );
		$this->assertContains( 'chart-js', $dash['deps'] );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );
	}

	public function test_import_export_not_enqueued_on_unrelated_admin_page(): void {
		Functions\when( 'do_action' )->alias(
			function ( $hook, ...$args ) {
				if ( $hook === 'admin_enqueue_scripts' ) {
					EnqueueAssets::enqueue_admin( ...$args );
				}
			}
		);
		$_GET['tab'] = 'import_export';

		// Simulate an admin page that is NOT an ArtPulse settings page:
		do_action( 'admin_enqueue_scripts', 'plugins.php' );

		$this->assertNull( $this->script( 'papaparse' ) );
		$this->assertNull( $this->script( 'ap-csv-import' ) );

		unset( $_GET['tab'] );
	}

	public function test_dashboard_not_enqueued_on_unrelated_admin_page(): void {
		// Provide assets that would normally load on the dashboard
		$this->touch( 'assets/css/dashboard.css' );
		$this->touch( 'assets/js/dashboard-role-tabs.js' );
		$this->touch( 'assets/js/role-dashboard.js' );
		$this->touch( 'assets/libs/chart.js/4.4.1/chart.min.js' );

		Functions\when( 'do_action' )->alias(
			function ( $hook, ...$args ) {
				if ( $hook === 'admin_enqueue_scripts' ) {
					EnqueueAssets::enqueue_admin( ...$args );
				}
			}
		);

		// Simulate an admin page that is NOT a dashboard page
		do_action( 'admin_enqueue_scripts', 'plugins.php' );

		// Chart.js should be registered but not enqueued
		$this->assertArrayHasKey( 'chart-js', $this->registeredScripts );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );

		// Dashboard assets should not load
		$this->assertNull( $this->style( 'ap-dashboard' ) );
		$this->assertNull( $this->script( 'ap-role-tabs' ) );
		$this->assertNull( $this->script( 'role-dashboard' ) );
	}

	public function test_settings_scripts_not_enqueued_on_unrelated_admin_page(): void {
		$this->touch( 'assets/js/ap-analytics.js' );
		$this->touch( 'assets/js/ap-user-dashboard.js' );
		$this->touch( 'assets/libs/chart.js/4.4.1/chart.min.js' );

		Functions\when( 'do_action' )->alias(
			function ( $hook, ...$args ) {
				if ( $hook === 'admin_enqueue_scripts' ) {
					EnqueueAssets::enqueue_admin( ...$args );
				}
			}
		);

		do_action( 'admin_enqueue_scripts', 'plugins.php' );

		$this->assertNull( $this->script( 'ap-analytics' ) );
		$this->assertNull( $this->script( 'ap-user-dashboard-js' ) );
		$this->assertArrayHasKey( 'chart-js', $this->registeredScripts );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );
	}

	public function test_org_dashboard_admin_enqueues_with_sortable(): void {
			// Provide dashboard assets
			$this->touch( 'assets/css/dashboard.css' );
			$this->touch( 'assets/js/dashboard-role-tabs.js' );
			$this->touch( 'assets/js/role-dashboard.js' );
			$this->touch( 'assets/libs/sortablejs/Sortable.min.js' );

		Functions\when( 'do_action' )->alias(
			function ( $hook, ...$args ) {
				if ( $hook === 'admin_enqueue_scripts' ) {
					EnqueueAssets::enqueue_admin( ...$args );
				}
			}
		);

			// Fire the org dashboard hook
			do_action( 'admin_enqueue_scripts', 'toplevel_page_ap-org-dashboard' );

			$this->assertNotNull( $this->script( 'ap-role-tabs' ) );
			$role = $this->script( 'role-dashboard' );
			$this->assertNotNull( $role );
			$this->assertContains( 'ap-role-tabs', $role['deps'] );
			$this->assertContains( 'sortablejs', $role['deps'] );
	}

		/**
		 * @return array<string, array{0: string}>
		 */
	public static function settings_pages_provider(): array {
			return array(
				'settings root' => array( 'toplevel_page_artpulse-settings' ),
				'import-export' => array( 'artpulse-settings_page_artpulse-import-export' ),
				'quickstart'    => array( 'artpulse-settings_page_artpulse-quickstart' ),
				'engagement'    => array( 'artpulse-settings_page_artpulse-engagement' ),
			);
	}

		/**
		 * @dataProvider settings_pages_provider
		 */
	public function test_role_dashboard_scripts_not_enqueued_on_settings_pages( string $hook ): void {
			$this->touch( 'assets/js/dashboard-role-tabs.js' );
			$this->touch( 'assets/js/role-dashboard.js' );

			EnqueueAssets::enqueue_admin( $hook );

			$this->assertNull( $this->script( 'ap-role-tabs' ) );
			$this->assertNull( $this->script( 'role-dashboard' ) );
	}

	public function test_dashboard_assets_not_double_enqueued(): void {
		$this->touch( 'assets/css/dashboard.css' );
		$this->touch( 'assets/js/dashboard-role-tabs.js' );
		$this->touch( 'assets/js/role-dashboard.js' );

		$scriptCalls = array();
		$styleCalls  = array();
		Functions\when( 'wp_enqueue_script' )->alias(
			function ( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) use ( &$scriptCalls ) {
				$scriptCalls[ $handle ] = ( $scriptCalls[ $handle ] ?? 0 ) + 1;
				if ( $src === '' && isset( $this->registeredScripts[ $handle ] ) ) {
					$r         = $this->registeredScripts[ $handle ];
					$src       = $r['src'];
					$deps      = $r['deps'];
					$ver       = $r['ver'];
					$in_footer = $r['in_footer'];
				}
				$this->enqueuedScripts[ $handle ] = array(
					'handle'    => $handle,
					'src'       => $src,
					'deps'      => $deps,
					'ver'       => $ver,
					'in_footer' => $in_footer,
				);
			}
		);
		Functions\when( 'wp_enqueue_style' )->alias(
			function ( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) use ( &$styleCalls ) {
				$styleCalls[ $handle ]           = ( $styleCalls[ $handle ] ?? 0 ) + 1;
				$this->enqueuedStyles[ $handle ] = array(
					'handle' => $handle,
					'src'    => $src,
					'deps'   => $deps,
					'ver'    => $ver,
					'media'  => $media,
				);
			}
		);

		EnqueueAssets::enqueue_admin( 'toplevel_page_ap-dashboard' );
		EnqueueAssets::enqueue_admin( 'toplevel_page_ap-dashboard' );

		$this->assertSame( 1, $styleCalls['ap-dashboard'] ?? 0 );
		$this->assertSame( 1, $scriptCalls['ap-role-tabs'] ?? 0 );
		$this->assertSame( 1, $scriptCalls['role-dashboard'] ?? 0 );
	}

	public function test_frontend_registers_chart_only(): void {
		$this->touch( 'assets/libs/chart.js/4.4.1/chart.min.js' );

		EnqueueAssets::enqueue_frontend();

		$this->assertArrayHasKey( 'chart-js', $this->registeredScripts );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );
		$this->assertSame( 1234567890, $this->registeredScripts['chart-js']['ver'] );
	}

	public function test_chart_js_semver_fallback_when_file_missing(): void {
		EnqueueAssets::enqueue_frontend();

		$this->assertArrayHasKey( 'chart-js', $this->registeredScripts );
		$this->assertArrayNotHasKey( 'chart-js', $this->enqueuedScripts );
		$this->assertSame( '4.4.1', $this->registeredScripts['chart-js']['ver'] );
	}
}
