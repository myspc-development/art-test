<?php
require_once __DIR__ . '/../TestStubs.php';

if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
        define( 'ARTPULSE_PLUGIN_DIR', dirname( __DIR__, 2 ) );
}
if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
        define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
}

// Allow tests to toggle gating conditions and auth checks.
$mock_query_var         = '';
$mock_is_page_dashboard = false;
$mock_is_user_logged_in = true;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Tests\Stubs\MockStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;

final class DashboardTemplateIncludeTest extends TestCase {
        protected function setUp(): void {
                global $mock_query_var, $mock_is_page_dashboard, $mock_is_user_logged_in;
                $mock_query_var             = '';
                $mock_is_page_dashboard     = false;
                $mock_is_user_logged_in     = true;
                $_GET                       = array();
                MockStorage::$current_roles = array();

                Monkey\setUp();
                Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => dirname( __DIR__, 2 ) . '/' );
                Functions\when( 'register_activation_hook' )->justReturn();
                Functions\when( 'add_rewrite_rule' )->justReturn();
                Functions\when( 'get_query_var' )->alias(
                        function ( $var ) use ( &$mock_query_var ) {
                                return $var === 'ap_dashboard' ? $mock_query_var : '';
                        }
                );
                Functions\when( 'is_page' )->alias(
                        function ( $slug ) use ( &$mock_is_page_dashboard ) {
                                return $slug === 'dashboard' && $mock_is_page_dashboard;
                        }
                );
                Functions\when( 'is_user_logged_in' )->alias(
                        function () use ( &$mock_is_user_logged_in ) {
                                return $mock_is_user_logged_in;
                        }
                );
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

	public function test_returns_dashboard_template_when_query_var_and_authorized(): void {
		global $mock_query_var;
		$mock_query_var             = '1';
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
		global $mock_query_var;
		$mock_query_var       = '1';
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
