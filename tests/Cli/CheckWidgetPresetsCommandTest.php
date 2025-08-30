<?php
declare(strict_types=1);

namespace ArtPulse\Tests\Stubs {



        if ( ! class_exists( DashboardWidgetRegistryStub::class, false ) ) {
                class DashboardWidgetRegistryStub {
                        private static array $widgets = array();
                        public static function set_widgets( array $widgets ): void {
                                self::$widgets = $widgets; }
                        public static function getById( string $id ) {
                                return self::$widgets[ $id ] ?? null; }
                }
        }
}

// No global bootstrapping here to avoid leaking stubs or aliases into other tests.

namespace ArtPulse\Cli\Tests {
        use PHPUnit\Framework\TestCase;
        use WP_CLI;
        use ArtPulse\Core\DashboardController;
        use ArtPulse\Core\DashboardWidgetRegistry;
        use ArtPulse\Tests\Stubs\DashboardControllerStub;

       /**
	* NOTE:
	* - Run this test under the UNIT suite (phpunit.unit.xml.dist).
	* - Exclude tests/Cli from the WP suite (phpunit.wp.xml.dist) to avoid loading real classes alongside stubs.
	*
	* @runInSeparateProcess
	*/
       class CheckWidgetPresetsCommandTest extends TestCase {
	       protected function setUp(): void {
	               if ( ! defined( 'ABSPATH' ) ) {
	                       define( 'ABSPATH', __DIR__ );
	               }

	               if ( ! function_exists( 'add_role' ) ) {
	                       function add_role( $role, $display_name = '', $caps = array() ) {
	                               $GLOBALS['test_roles'][ $role ] = $caps;
	                       }
	               }
	               if ( ! function_exists( 'remove_role' ) ) {
	                       function remove_role( $role ) {
	                               unset( $GLOBALS['test_roles'][ $role ] );
	                       }
	               }
	               if ( ! function_exists( 'get_role' ) ) {
	                       function get_role( $role ) {
	                               $caps = $GLOBALS['test_roles'][ $role ] ?? array();
	                               return new class( $caps ) {
	                                       private array $caps;
	                                       public function __construct( array $caps ) {
	                                               $this->caps = $caps;
	                                       }
	                                       public function has_cap( $cap ) {
	                                               return ! empty( $this->caps[ $cap ] );
	                                       }
	                               };
	                       }
	               }

                       if ( ! class_exists( \ArtPulse\Core\DashboardController::class, false ) ) {
                               class_alias( DashboardControllerStub::class, DashboardController::class );
                       }
                       if ( ! class_exists( \ArtPulse\Core\DashboardWidgetRegistry::class, false ) ) {
                               class_alias( \ArtPulse\Tests\Stubs\DashboardWidgetRegistryStub::class, \ArtPulse\Core\DashboardWidgetRegistry::class );
                       }

                       require_once __DIR__ . '/../../src/Core/WidgetAccessValidator.php';
                       require_once __DIR__ . '/../../includes/class-cli-check-widget-presets.php';

	               WP_CLI::$commands      = array();
	               WP_CLI::$last_output   = '';
	               $GLOBALS['test_roles'] = array();
	       }

	       protected function tearDown(): void {
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
                                       'widget_valid_widget' => array( 'roles' => array( 'member' ) ),
                                       'widget_cap_widget'   => array(
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
                               $this->assertSame( 'All widget presets look good.' . PHP_EOL, WP_CLI::$last_output );
                       } catch ( \Exception $e ) {
                               $this->assertSame( 'Preset check found issues.', $e->getMessage() );
                       }
               }
       }
}
