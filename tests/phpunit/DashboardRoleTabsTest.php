<?php
if ( ! function_exists( 'ap_dashboard_v2_enabled' ) ) {
	function ap_dashboard_v2_enabled() {
		return true; }
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

final class DashboardRoleTabsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'wp_get_current_user' )->justReturn( (object) array( 'roles' => array( 'member', 'artist', 'organization' ) ) );
		Functions\when( 'esc_html' )->alias( fn( $s ) => $s );
		Functions\when( 'esc_attr' )->alias( fn( $s ) => $s );
		Functions\when( 'esc_attr__' )->alias( fn( $s, $d = null ) => $s );
		Functions\when( 'sanitize_key' )->alias( fn( $k ) => strtolower( preg_replace( '/[^a-z0-9_]/', '', $k ) ) );
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
