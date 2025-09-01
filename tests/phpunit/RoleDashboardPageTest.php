<?php

namespace ArtPulse\Admin { 
    // --- WordPress function stubs ---
    if ( ! function_exists( __NAMESPACE__ . '\\add_submenu_page' ) ) {
        function add_submenu_page( ...$args ) {
            \ArtPulse\Admin\Tests\RoleDashboardPageTest::$add_submenu_page_args = $args;
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\__' ) ) {
        function __( $text, $domain = null ) {
            return $text;
        }
    }
    if ( ! function_exists( __NAMESPACE__ . '\\get_current_user_id' ) ) {
        function get_current_user_id() {
            return 1;
        }
    }
}

namespace {
    require_once dirname( __DIR__, 2 ) . '/includes/helpers.php';
}

namespace ArtPulse\Admin\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\RoleDashboardPage;
use function Patchwork\redefine;
use function Patchwork\restore;

/**

 * @group PHPUNIT

 */

final class RoleDashboardPageTest extends TestCase {
    public static array $add_submenu_page_args = array();
    public static array $render_roles = array();
    public static ?string $enqueued_role = null;
    private $renderDashboardHandle;

    protected function setUp(): void {
        self::$add_submenu_page_args = array();
        self::$render_roles = array();
        self::$enqueued_role = null;
        $this->renderDashboardHandle = redefine( 'ap_render_dashboard', function ( $roles ) {
            self::$render_roles = $roles;
        } );
    }

    protected function tearDown(): void {
        restore( $this->renderDashboardHandle );
    }

    public function test_render_callback_executes(): void {
        RoleDashboardPage::add_page();
        $this->assertNotEmpty( self::$add_submenu_page_args );
        $callback = self::$add_submenu_page_args[5];
        $this->assertIsCallable( $callback );

        $roleHandle = redefine( 'ArtPulse\\Core\\DashboardController::get_role', fn() => 'member' );
        $enqueueHandle = redefine( 'ArtPulse\\Frontend\\ShortcodeRoleDashboard::enqueue_assets', function ( $role ) {
            self::$enqueued_role = $role;
        } );

        ob_start();
        $callback();
        ob_end_clean();

        $this->assertSame( array( 'member' ), self::$render_roles );
        $this->assertSame( 'member', self::$enqueued_role );

        restore( $roleHandle );
        restore( $enqueueHandle );
    }
}
}
