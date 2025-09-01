<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardController;

/**

 * @group integration

 */

class DashboardCapabilityGateTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		remove_filter( 'template_include', array( DashboardController::class, 'interceptTemplate' ), 9 );
		DashboardController::init();
		$_GET = array();
		set_query_var( 'ap_dashboard', null );
	}

	private function request_dashboard(): string {
		set_query_var( 'ap_dashboard', '1' );
		$_GET['ap_dashboard'] = '1';
		return apply_filters( 'template_include', 'index.php' );
	}

	public function test_dashboard_template_access_granted_with_capability(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = new \WP_User( $user_id );
		$user->add_cap( 'view_artpulse_dashboard' );
		wp_set_current_user( $user_id );

		$template = $this->request_dashboard();

		$this->assertStringContainsString( 'templates/simple-dashboard.php', $template );
	}

	public function test_dashboard_template_access_denied_without_capability(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		header_remove();

		$template = $this->request_dashboard();

		$this->assertSame( 'index.php', $template );
		$this->assertEmpty( headers_list() );
	}
}
