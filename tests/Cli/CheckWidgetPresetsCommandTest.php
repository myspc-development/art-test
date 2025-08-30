<?php
declare(strict_types=1);

namespace {
	// ABSPATH for unit context; WP-CLI stub loaded via bootstrap
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ ); }

	// Minimal role handling for capability checks.
	if ( ! function_exists( 'add_role' ) ) {
		function add_role( $role, $display_name = '', $caps = array() ) {
			$GLOBALS['test_roles'][ $role ] = $caps; }
	}
	if ( ! function_exists( 'remove_role' ) ) {
		function remove_role( $role ) {
			unset( $GLOBALS['test_roles'][ $role ] ); }
	}
	if ( ! function_exists( 'get_role' ) ) {
		function get_role( $role ) {
			$caps = $GLOBALS['test_roles'][ $role ] ?? array();
			return new class($caps) {
				private array $caps;
				public function __construct( array $caps ) {
					$this->caps = $caps; }
				public function has_cap( $cap ) {
					return ! empty( $this->caps[ $cap ] ); }
			};
		}
	}
}

namespace ArtPulse\Tests\Stubs {
	/**
	 * Stubbed versions live in a separate namespace to avoid collisions.
	 * We will class_alias them to the production FQCNs only if those do not exist.
	 */
	class DashboardControllerStub {
		private static array $presets = array();
		public static function set_presets( array $presets ): void {
			self::$presets = $presets; }
		public static function get_default_presets(): array {
			return self::$presets; }
		public static function get_role( int $user_id = 0 ): string {
			return 'member'; }
	}

	class DashboardWidgetRegistryStub {
		private static array $widgets = array();
		public static function set_widgets( array $widgets ): void {
			self::$widgets = $widgets; }
		public static function getById( string $id ) {
			return self::$widgets[ $id ] ?? null; }
	}
}

namespace {
	// Safely alias stubs to the real FQCNs if the real classes are not loaded.
	if ( ! class_exists( \ArtPulse\Core\DashboardController::class, /*autoload*/ false ) ) {
		class_alias( \ArtPulse\Tests\Stubs\DashboardControllerStub::class, \ArtPulse\Core\DashboardController::class );
	}
	if ( ! class_exists( \ArtPulse\Core\DashboardWidgetRegistry::class, /*autoload*/ false ) ) {
		class_alias( \ArtPulse\Tests\Stubs\DashboardWidgetRegistryStub::class, \ArtPulse\Core\DashboardWidgetRegistry::class );
	}

	// Load the CLI command after aliases are in place.
	require_once __DIR__ . '/../../includes/class-cli-check-widget-presets.php';
}

namespace ArtPulse\Cli\Tests {
	use PHPUnit\Framework\TestCase;
	use WP_CLI;
	use ArtPulse\Core\DashboardController;
	use ArtPulse\Core\DashboardWidgetRegistry;

	/**
	 * NOTE:
	 * - Run this test under the UNIT suite (phpunit.unit.xml.dist).
	 * - Exclude tests/Cli from the WP suite (phpunit.wp.xml.dist) to avoid loading real classes alongside stubs.
	 */
	class CheckWidgetPresetsCommandTest extends TestCase {
		protected function setUp(): void {
			WP_CLI::$commands      = array();
			WP_CLI::$last_output   = '';
			$GLOBALS['test_roles'] = array();
		}

		public function test_reports_warnings_and_errors(): void {
			// Setup roles
			add_role( 'member', 'Member', array( 'read' => true ) );

			// Setup widgets
			DashboardWidgetRegistry::set_widgets(
				array(
					'valid_widget' => array( 'roles' => array( 'member' ) ),
					'cap_widget'   => array(
						'roles'      => array( 'member' ),
						'capability' => 'manage_options',
					),
				)
			);

			// Setup presets with valid, missing, and capability restricted widgets
			DashboardController::set_presets(
				array(
					'member_preset' => array(
						'role'   => 'member',
						'layout' => array(
							array( 'id' => 'valid_widget' ),
							array( 'id' => 'missing_widget' ),
							array( 'id' => 'cap_widget' ),
						),
					),
				)
			);

			WP_CLI::add_command( 'artpulse check-widget-presets', \AP_CLI_Check_Widget_Presets::class );

			try {
				WP_CLI::runcommand( 'artpulse check-widget-presets' );
				$this->fail( 'Expected error not thrown' );
			} catch ( \RuntimeException $e ) {
				$this->assertSame( 'Preset check found issues.', $e->getMessage() );
				$out = WP_CLI::$last_output;
				$this->assertStringContainsString( 'missing_widget not registered in preset member_preset', $out );
				$this->assertStringContainsString( 'cap_widget requires capability manage_options not available to member in preset member_preset', $out );
			}
		}
	}
}
