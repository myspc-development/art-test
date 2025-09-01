<?php
namespace ArtPulse\Admin\Tests;

use WP_UnitTestCase;
use ArtPulse\Admin\EngagementDashboard;

/**
 * @group ADMIN
 */
class EngagementDashboardTest extends WP_UnitTestCase {

	private int $admin_id;
	private int $subscriber_id;

	public function set_up() {
		parent::set_up();
		$this->admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $this->admin_id );
	}

	private function render_dashboard(): string {
		$_GET = array();
		ob_start();
		EngagementDashboard::render();
		return ob_get_clean();
	}

	public function test_premium_sections_hidden_without_cap(): void {
		$html = $this->render_dashboard();
		$this->assertStringContainsString( 'Upgrade to access premium analytics', $html );
		$this->assertStringNotContainsString( 'id="apEngagementChart"', $html );
	}

	public function test_premium_sections_visible_with_cap(): void {
		$user = new \WP_User( $this->admin_id );
		$user->add_cap( 'ap_premium_member' );
		$html = $this->render_dashboard();
		$this->assertStringContainsString( 'id="apEngagementChart"', $html );
	}
}
