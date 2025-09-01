<?php
declare(strict_types=1);

namespace ArtPulse\Tests\Stubs {
	/**
	 * Minimal, in-process registry stub with the common surface used by the CLI check.
	 * NOTE: Keep this lean so it doesn't leak unexpected behavior into other unit tests.
	 */
	if ( ! class_exists( DashboardWidgetRegistryStub::class, false ) ) {
		/**
		 * @group CLI
		 */
		class DashboardWidgetRegistryStub {
			/** @var array<string,array> */
			private static array $widgets = array();

			public static function set_widgets( array $widgets ): void {
				self::$widgets = $widgets;
			}

			public static function getById( string $id ) {
				return self::$widgets[ self::canon_slug( $id ) ] ?? null;
			}

			/** Return all raw widget defs (already canonical keys). */
			public static function get_all(): array {
				return self::$widgets;
			}

			public static function canon_slug( string $slug ): string {
				if ($slug === '') {
					return '';
				}
				$slug = strtolower($slug);
				if (strpos($slug, 'widget_') !== 0) {
					$slug = 'widget_' . preg_replace('/^widget_/', '', $slug);
				}
				return $slug;
			}

			public static function exists( string $slug ): bool {
				return isset( self::$widgets[ self::canon_slug( $slug ) ] );
			}

			/** Legacy alias some code may call. */
			public static function has( string $slug ): bool {
				return self::exists( $slug );
			}
		}
	}
	// No global bootstrapping here to avoid leaking stubs or aliases into other tests.
}

namespace ArtPulse\Cli\Tests {
	use PHPUnit\Framework\TestCase;
	use WP_CLI;

	use ArtPulse\Core\DashboardController;
	use ArtPulse\Core\DashboardWidgetRegistry;
	use ArtPulse\Tests\Stubs\DashboardControllerStub;
	use ArtPulse\Tests\Stubs\DashboardWidgetRegistryStub;

	/**
	 * Run under the unit suite (phpunit.unit.xml.dist).
	 * We alias real classes to stubs and invoke the CLI command, then assert on its tabular output.
	 *
	 * @runInSeparateProcess
	 */
	class CheckWidgetPresetsCommandTest extends TestCase {

		protected function setUp(): void {
			// Provide ABSPATH for any include checks in CLI file.
			if ( ! defined( 'ABSPATH' ) ) {
				define( 'ABSPATH', __DIR__ );
			}

			// Use the unit WP-CLI stub (autoloaded by dev autoload); guard in case it isn't loaded yet.
			if ( ! class_exists('\WP_CLI') && file_exists(__DIR__ . '/../../tests/Support/WpCliStub.php') ) {
				require_once __DIR__ . '/../../tests/Support/WpCliStub.php';
			}

			// Map core classes to lightweight stubs for this isolated test.
			if ( ! class_exists( \ArtPulse\Core\DashboardController::class, false ) ) {
				class_alias( DashboardControllerStub::class, DashboardController::class );
			}
			if ( ! class_exists( \ArtPulse\Core\DashboardWidgetRegistry::class, false ) ) {
				class_alias( DashboardWidgetRegistryStub::class, DashboardWidgetRegistry::class );
			}

			// Pull in the command & any tiny validators it expects.
			if ( file_exists(__DIR__ . '/../../src/Core/WidgetAccessValidator.php') ) {
				require_once __DIR__ . '/../../src/Core/WidgetAccessValidator.php';
			}
			require_once __DIR__ . '/../../includes/class-cli-check-widget-presets.php';

			// Fresh WP_CLI state each test.
			WP_CLI::$commands    = array();
			WP_CLI::$last_output = '';
		}

		protected function tearDown(): void {
			WP_CLI::$commands    = array();
			WP_CLI::$last_output = '';
		}

		public function test_reports_warnings_and_errors(): void {
			// 1) Registry: one valid, one restricted (capability), and intentionally leave one "missing".
			DashboardWidgetRegistry::set_widgets(
				array(
					'widget_valid_widget' => array(
						'label' => 'Valid',
						'roles' => array( 'member' ),
						'callback' => '__return_null',
					),
					'widget_cap_widget'   => array(
						'label' => 'Needs Cap',
						'roles' => array( 'member' ),
						'capability' => 'manage_options',
						'callback' => '__return_null',
					),
					// NOTE: do NOT register "widget_missing_widget"
				)
			);

			// 2) Preset references: valid, missing, and restricted IDs (unprefixed; command should canonicalize).
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

                        // 3) Register the command exactly as production code does.
                        WP_CLI::add_command( 'artpulse check-widget-presets', \AP_CLI_Check_Widget_Presets::class );

                        // 4) Run & capture output.
                        try {
                                WP_CLI::runcommand( 'artpulse check-widget-presets' );
                        } catch ( \WP_CLI\ExitException $e ) {
                                // Command signals issues via ExitException; continue with captured output.
                        }
                        $out = WP_CLI::$last_output;

                        // Header should mention widget/action columns.
                        $this->assertNotEmpty( $out, 'CLI produced no output' );
                        $this->assertStringContainsString( 'widget', $out );
                        $this->assertStringContainsString( 'action', $out );

                        // Expected widget id and actions present.
                        $this->assertStringContainsString( 'widget_missing_widget', $out );
                        $this->assertStringContainsString( 'unhide', $out );
                        $this->assertStringContainsString( 'activate', $out );
                        $this->assertStringContainsString( 'bind', $out );
                        $this->assertStringContainsString( 'ArtPulse\\Widgets\\TestWidget', $out );

                        // Split on newlines and ensure at least header + three rows.
                        $lines = array_values( array_filter( array_map( 'trim', explode( "\n", $out ) ) ) );
                        $this->assertGreaterThanOrEqual( 4, count( $lines ), 'Expected header and three rows' );
                        $this->assertStringContainsString( "\t", $lines[0], 'Header should be tab-separated' );
                }
        }
}
