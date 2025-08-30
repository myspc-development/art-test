<?php
declare(strict_types=1);

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/../Support/DashboardControllerStub.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;
use ArtPulse\Tests\Stubs\MockStorage;
use function Patchwork\redefine;
use function Patchwork\restore;
use Brain\Monkey;
use Brain\Monkey\Functions;

if ( ! defined( 'AP_VERBOSE_DEBUG' ) ) {
	define( 'AP_VERBOSE_DEBUG', true );
}
if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
        define( 'ARTPULSE_PLUGIN_DIR', dirname( __DIR__, 2 ) );
}
if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
        define( 'ARTPULSE_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/artpulse.php' );
}
if ( ! function_exists( 'is_page' ) ) {
	function is_page( $slug ) {
		return false; }
}
if ( ! function_exists( 'is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true; }
}
if ( ! function_exists( 'current_user_can' ) ) {
        function current_user_can( $cap ) {
                return true; }
}

if ( ! function_exists( 'register_activation_hook' ) ) {
        function register_activation_hook( $file, $callback ) {}
}

if ( ! class_exists( 'WP_Query' ) ) {
        class WP_Query {
                public function get( $key ) {
                        return '';
                }
        }
}

final class DashboardRoleSwitchTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                Functions\when( 'get_query_var' )->alias( fn( $key ) => $_GET[ $key ] ?? '' );

                DashboardPresets::resetCache();
                MockStorage::$current_roles = array( 'view_artpulse_dashboard', 'artist' );
                ini_set( 'error_log', '/tmp/phpunit-error.log' );
                WidgetRegistry::register( 'widget_membership', [self::class, 'renderSection'] );
                WidgetRegistry::register( 'widget_artist_revenue_summary', [self::class, 'renderSection'] );
                WidgetRegistry::register( 'widget_audience_crm', [self::class, 'renderSection'] );
        }

        protected function tearDown(): void {
                DashboardPresets::resetCache();
                Monkey\tearDown();
                parent::tearDown();
        }

	/**
	 * @dataProvider roles
	 */
	public function test_role_param_sets_header_and_attributes( string $role ): void {
                $_GET['ap_dashboard'] = '1';
                $_GET['role']         = $role;
                $captured             = array();
                $handle               = redefine(
                        'header',
                        function ( $string ) use ( &$captured ) {
                                $captured[] = $string;
                        }
                );
                $initHandle           = redefine(
                        '\\ArtPulse\\Core\\DashboardWidgetRegistry::init',
                        static function (): void {}
                );
                $headerHook           = null;
                $addHandle            = redefine(
                        'add_action',
                        function ( $hook, $callback ) use ( &$headerHook ) {
                                if ( $hook === 'send_headers' ) {
                                        $headerHook = $callback;
                                }
                        }
                );
                $q = new WP_Query();
                DashboardController::resolveRoleIntoQuery( $q );
                $tpl = DashboardController::interceptTemplate( 'index.php' );
                if ( is_callable( $headerHook ) ) {
                        $headerHook();
                }
                ob_start();
                include $tpl;
                $html = ob_get_clean();

		$this->assertStringContainsString( sprintf( 'id="ap-panel-%s"', $role ), $html );
		$this->assertStringContainsString( sprintf( 'aria-labelledby="ap-tab-%s"', $role ), $html );
		$this->assertStringContainsString( sprintf( 'data-role="%s"', $role ), $html );

                $this->assertContains( 'X-AP-Resolved-Role: ' . $role, $captured );

                restore( $addHandle );
                restore( $initHandle );
                restore( $handle );
                unset( $_GET['role'], $_GET['ap_role'], $_GET['ap_dashboard'] );
	}

        public function roles(): array {
                return array( array( 'member' ), array( 'artist' ), array( 'organization' ) );
        }

        public static function renderSection(): string { return '<section></section>'; }
 }
