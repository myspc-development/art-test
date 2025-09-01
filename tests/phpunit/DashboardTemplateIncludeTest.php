<?php
require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../Support/Stubs/DashboardControllerStub.php';

if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
        define( 'ARTPULSE_PLUGIN_DIR', dirname( __DIR__, 2 ) );
}
if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
        define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
}

// Allow tests to toggle gating conditions and auth checks.
$mock_is_page_dashboard = false;
$mock_is_user_logged_in = true;


use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Tests\Stubs\MockStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;

if ( ! function_exists( 'is_page' ) ) {
        function is_page( $slug = '' ) {
                global $mock_is_page_dashboard;
                return 'dashboard' === $slug && $mock_is_page_dashboard;
        }
}
if ( ! function_exists( 'is_user_logged_in' ) ) {
        function is_user_logged_in() {
                global $mock_is_user_logged_in;
                return (bool) $mock_is_user_logged_in;
        }
}
if ( ! function_exists( 'current_user_can' ) ) {
        function current_user_can( $cap ) {
                return in_array( $cap, MockStorage::$current_roles, true );
        }
}

/**
 * @runInSeparateProcess
 */
/**
 * @group phpunit
 */
final class DashboardTemplateIncludeTest extends TestCase {
        protected function setUp(): void {
                if ( ! class_exists( \ArtPulse\Core\DashboardController::class, false ) ) {
                        class_alias( \ArtPulse\Tests\Stubs\DashboardControllerStub::class, \ArtPulse\Core\DashboardController::class );
                }
                global $mock_is_page_dashboard, $mock_is_user_logged_in;
                $mock_is_page_dashboard     = false;
                $mock_is_user_logged_in     = true;
                $_GET                       = array();
                MockStorage::$current_roles = array();

                Monkey\setUp();
                Functions\when( 'is_page' )->alias( fn( $slug = '' ) => 'dashboard' === $slug && $GLOBALS['mock_is_page_dashboard'] );
                Functions\when( 'is_user_logged_in' )->alias( fn() => (bool) $GLOBALS['mock_is_user_logged_in'] );
                Functions\when( 'current_user_can' )->alias( fn( $cap ) => in_array( $cap, MockStorage::$current_roles, true ) );
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

        public function test_returns_dashboard_template_when_query_var_and_authorized(): void {
                $_GET['ap_dashboard']       = '1';
                MockStorage::$current_roles = array( 'view_artpulse_dashboard' );
                $tpl                        = DashboardController::interceptTemplate( 'index.php' );
                $this->assertStringContainsString( 'templates/simple-dashboard.php', $tpl );
        }

	public function test_returns_dashboard_template_when_slug_and_authorized(): void {
		global $mock_is_page_dashboard;
		$mock_is_page_dashboard     = true;
		MockStorage::$current_roles = array( 'view_artpulse_dashboard' );
		$tpl                        = DashboardController::interceptTemplate( 'index.php' );
		$this->assertStringContainsString( 'templates/simple-dashboard.php', $tpl );
	}

        public function test_returns_original_template_when_unauthorized(): void {
                $_GET['ap_dashboard'] = '1';
                // User logged in but lacks capability
                $tpl = DashboardController::interceptTemplate( 'index.php' );
                $this->assertSame( 'index.php', $tpl );
        }

	public function test_does_not_intercept_unrelated_routes(): void {
		MockStorage::$current_roles = array( 'view_artpulse_dashboard' );
		$tpl                        = DashboardController::interceptTemplate( 'index.php' );
		$this->assertSame( 'index.php', $tpl );
	}
}
