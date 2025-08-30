<?php
require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use Brain\Monkey;
use Brain\Monkey\Functions;

if ( ! function_exists( 'is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true; }
}

final class DashboardRoleTemplateAttributesTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => dirname( __DIR__, 2 ) . '/' );
                Functions\when( 'get_query_var' )->alias( fn( $key ) => $_GET[ $key ] ?? '' );
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

        /** @dataProvider roles */
        public function test_section_attributes_match_role( string $role ): void {
                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
                }
                require_once dirname( __DIR__, 2 ) . '/includes/widget-loader.php';
                DashboardWidgetRegistry::init();
                $_GET['ap_role'] = $role;
                ob_start();
                include __DIR__ . '/../../templates/simple-dashboard.php';
                $html = ob_get_clean();
                $this->assertStringContainsString( sprintf( 'id="ap-panel-%s"', $role ), $html );
                $this->assertStringContainsString( sprintf( 'aria-labelledby="ap-tab-%s"', $role ), $html );
                $this->assertStringContainsString( sprintf( 'data-role="%s"', $role ), $html );
                unset( $_GET['ap_role'] );
        }

        public function roles(): array {
                return array( array( 'member' ), array( 'artist' ), array( 'organization' ) );
        }
}
