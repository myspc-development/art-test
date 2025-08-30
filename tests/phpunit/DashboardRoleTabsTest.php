<?php
if ( ! function_exists( 'ap_dashboard_v2_enabled' ) ) {
        function ap_dashboard_v2_enabled() {
                return true; }
}

if ( ! function_exists( 'sanitize_key' ) ) {
        function sanitize_key( $key ) {
                return strtolower( preg_replace( '/[^a-z0-9_]/', '', $key ) );
        }
}

if ( ! function_exists( 'esc_attr__' ) ) {
        function esc_attr__( $text, $domain = null ) {
                return $text;
        }
}

if ( ! function_exists( 'esc_attr' ) ) {
        function esc_attr( $text ) {
                return $text;
        }
}

if ( ! function_exists( 'esc_html' ) ) {
        function esc_html( $text ) {
                return $text;
        }
}

if ( ! function_exists( '__' ) ) {
        function __( $text, $domain = null ) {
                return $text;
        }
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * @runTestsInSeparateProcesses
 */
final class DashboardRoleTabsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
                Functions\when( 'wp_get_current_user' )->justReturn( (object) array( 'roles' => array( 'member', 'artist', 'organization' ) ) );
                Functions\when( 'wp_unslash' )->alias( fn( $v ) => $v );
        }

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_query_role_sets_active_tab(): void {
		$_GET['role'] = 'organization';
		ob_start();
		include dirname( __DIR__, 2 ) . '/templates/partials/dashboard-role-tabs.php';
		$html = ob_get_clean();
		unset( $_GET['role'] );
		$this->assertStringContainsString( 'data-role="organization"', $html );
		$this->assertMatchesRegularExpression( '/aria-selected="true"[^>]*data-role="organization"/', $html );
	}
}
