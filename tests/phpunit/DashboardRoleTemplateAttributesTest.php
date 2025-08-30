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
if ( ! function_exists( 'get_query_var' ) ) {
	function get_query_var( $key ) {
		return $_GET[ $key ] ?? ''; }
}

final class DashboardRoleTemplateAttributesTest extends TestCase {

        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => dirname( __DIR__, 2 ) . '/' );
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

        /** @dataProvider roles */
        public function test_section_attributes_match_role( string $role ): void {
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
