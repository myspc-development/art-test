<?php

namespace {
	// WP-CLI stub loaded via bootstrap
        $GLOBALS['hidden_widgets'] = array();
        $GLOBALS['options']        = array();

        if ( ! function_exists( 'apply_filters' ) ) {
                function apply_filters( $hook, $value, $role ) {
                        if ( 'ap_dashboard_hidden_widgets' === $hook ) {
                                return $GLOBALS['hidden_widgets'][ $role ] ?? $value;
                        }
                        return $value;
                }
        }

        if ( ! function_exists( 'get_option' ) ) {
                function get_option( $name, $default = false ) {
                        return $GLOBALS['options'][ $name ] ?? $default;
                }
        }

        if ( ! function_exists( 'update_option' ) ) {
                function update_option( $name, $value ) {
                        $GLOBALS['options'][ $name ] = $value;
                }
        }

        if ( ! function_exists( '__return_true' ) ) {
                function __return_true() {
                        return true;
                }
        }
}

namespace ArtPulse\Core {
        if ( ! class_exists( DashboardWidgetRegistry::class ) ) {
                /**
                 * @group CLI
                 */
                class DashboardWidgetRegistry {
                private static array $widgets = array();

		public static function set( array $widgets ): void {
			self::$widgets = $widgets;
		}

		public static function all(): array {
			return self::$widgets;
		}

		public static function update_widget( string $id, array $definition ): void {
			self::$widgets[ $id ] = array_merge( self::$widgets[ $id ] ?? array(), $definition );
                }

                public static function canon_slug( string $slug ): string {
                        return $slug;
                }
        }
        }
}

namespace ArtPulse\Cli\Tests {
        use PHPUnit\Framework\TestCase;
        use ArtPulse\Core\DashboardWidgetRegistry;
        use WP_CLI;
        use ArtPulse\Tests\WpTeardownTrait;

	require_once __DIR__ . '/../../src/Cli/WidgetDoctor.php';

        class WidgetDoctorCommandTest extends TestCase {
                use WpTeardownTrait;

                protected function setUp(): void {
                        WP_CLI::$commands          = array();
                        WP_CLI::$last_output       = '';
                        $GLOBALS['hidden_widgets'] = array();
                        $GLOBALS['options']        = array();
                        DashboardWidgetRegistry::set( array() );
                        WP_CLI::add_command( 'artpulse widgets', \ArtPulse\Cli\WidgetDoctor::class );
                }

                protected function tearDown(): void {
                        $this->reset_wp_state();
                        WP_CLI::$commands    = array();
                        WP_CLI::$last_output = '';
                        $GLOBALS['hidden_widgets'] = array();
                        $GLOBALS['options']        = array();
                }

                public function test_list_outputs_table(): void {
                        if ( ! class_exists( 'WP_CLI' ) ) {
                                $this->markTestSkipped( 'WP_CLI is not available.' );
                        }
                        DashboardWidgetRegistry::set(
                                array(
                                        'widget_one' => array(
						'status'   => 'active',
						'roles'    => array( 'member' ),
						'callback' => '__return_true',
					),
					'widget_two' => array(
						'status'   => 'beta',
						'roles'    => array( 'artist' ),
						'callback' => '__return_true',
					),
				)
			);
			$out = WP_CLI::runcommand( 'artpulse widgets list' );
			$this->assertStringContainsString( "id\troles", $out );
			$this->assertStringContainsString( "widget_two\tartist", $out );
		}

                public function test_list_supports_json(): void {
                        if ( ! class_exists( 'WP_CLI' ) ) {
                                $this->markTestSkipped( 'WP_CLI is not available.' );
                        }
                        DashboardWidgetRegistry::set(
                                array(
                                        'widget_one' => array(
						'status'   => 'active',
						'roles'    => array( 'member' ),
						'callback' => '__return_true',
					),
					'widget_two' => array(
						'status'   => 'beta',
						'roles'    => array( 'artist' ),
						'callback' => '__return_true',
					),
				)
			);
			$out     = WP_CLI::runcommand( 'artpulse widgets list --format=json' );
			$decoded = json_decode( $out, true );
			$this->assertSame( 'artist', $decoded[1]['roles'] );
		}

                public function test_audit_reports_issues_and_errors(): void {
                        if ( ! class_exists( 'WP_CLI' ) ) {
                                $this->markTestSkipped( 'WP_CLI is not available.' );
                        }
                        DashboardWidgetRegistry::set(
                                array(
                                        'widget_ok'     => array(
						'status'   => 'active',
						'callback' => '__return_true',
					),
					'widget_bad'    => array(
						'status'   => 'active',
						'callback' => 'missing_cb',
					),
					'widget_hidden' => array(
						'status'   => 'coming_soon',
						'callback' => '__return_true',
					),
				)
			);
			$GLOBALS['hidden_widgets']['artist'] = array( 'widget_hidden' );
			try {
				WP_CLI::runcommand( 'artpulse widgets audit' );
				$this->fail( 'Expected error not thrown' );
			} catch ( \WP_CLI\ExitException $e ) {
				$this->assertSame( 'Widget issues found.', $e->getMessage() );
				$out = WP_CLI::$last_output;
				$this->assertStringContainsString( 'widget_bad', $out );
				$this->assertStringContainsString( 'widget_hidden', $out );
			}
		}

                public function test_fix_unhides_and_activates(): void {
                        if ( ! class_exists( 'WP_CLI' ) ) {
                                $this->markTestSkipped( 'WP_CLI is not available.' );
                        }
                        DashboardWidgetRegistry::set(
                                array(
                                        'widget_fix' => array(
						'status'   => 'coming_soon',
						'callback' => '__return_true',
					),
				)
			);
			$GLOBALS['options']['artpulse_dashboard_hidden_member'] = array( 'widget_fix' );
			$GLOBALS['options']['artpulse_widget_status']           = array();
			$out = WP_CLI::runcommand( 'artpulse widgets fix --role=member --activate-all --unhide' );
			$this->assertStringContainsString( 'no placeholders will appear for member', $out );
			$this->assertSame( array(), $GLOBALS['options']['artpulse_dashboard_hidden_member'] );
			$this->assertArrayHasKey( 'widget_fix', $GLOBALS['options']['artpulse_widget_status'] );
			$widgets = DashboardWidgetRegistry::all();
			$this->assertSame( 'active', $widgets['widget_fix']['status'] );
		}
	}
}
