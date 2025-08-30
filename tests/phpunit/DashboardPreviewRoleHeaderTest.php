<?php
declare(strict_types=1);

require_once __DIR__ . '/../TestStubs.php';

if ( ! defined( 'AP_VERBOSE_DEBUG' ) ) {
	define( 'AP_VERBOSE_DEBUG', true );
}
if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
        define( 'ARTPULSE_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/artpulse.php' );
}
if ( ! function_exists( 'locate_template' ) ) {
        function locate_template( $template ) {
                return ''; }
}
if ( ! function_exists( 'is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true; }
}
if ( ! function_exists( 'esc_attr__' ) ) {
        function esc_attr__( $text, $domain = null ) {
                return $text; }
}

require_once dirname( __DIR__, 2 ) . '/includes/helpers.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Tests\Stubs\MockStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;

final class DashboardPreviewRoleHeaderTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => dirname( $file ) . '/' );

                MockStorage::$users[1]      = (object) array( 'roles' => array( 'administrator' ) );
                MockStorage::$current_roles = array( 'manage_options' );
                MockStorage::$user_meta     = array();
                MockStorage::$options       = array( 'ap_dashboard_option' => 'value' );
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

	/** @runInSeparateProcess */
	public function test_preview_role_sets_header_and_attributes_without_persisting(): void {
                $_GET['ap_preview_role']  = 'artist';
                $_GET['ap_preview_nonce'] = 'nonce_ap_preview';

                $roleHandle = \Patchwork\redefine(
                        'ap_get_effective_role',
                        function () {
                                return \ArtPulse\Core\RoleResolver::resolve();
                        }
                );

                require_once __DIR__ . '/../ap_get_effective_role_stub.php';

                Functions\when( 'register_activation_hook' )->alias( fn() => null );
                Functions\when( 'add_rewrite_rule' )->alias( fn() => null );

                ob_start();
                ap_render_dashboard();
                ob_end_clean();

                $resolved  = ap_get_effective_role();
                $user_role = \ArtPulse\Core\RoleResolver::resolve();
                ob_start();
                include dirname( __DIR__, 2 ) . '/templates/dashboard-role.php';
                $html = ob_get_clean();

                \Patchwork\restore( $roleHandle );
                unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );

                $this->assertSame( 'artist', $resolved );
                $this->assertStringContainsString( 'data-role="artist"', $html );

                $this->assertArrayNotHasKey( 'ap_dashboard_layout', MockStorage::$user_meta[1] ?? array() );
                $this->assertSame( 'value', MockStorage::$options['ap_dashboard_option'] );
        }
}
