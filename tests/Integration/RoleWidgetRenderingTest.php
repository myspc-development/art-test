<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardController;

/**
 * @group INTEGRATION
 */
class RoleWidgetRenderingTest extends \WP_UnitTestCase {
	public function test_member_widgets_present(): void {
		$widgets = DashboardController::get_widgets_for_role( 'member' );
		$this->assertNotEmpty( $widgets );
	}

	public function test_artist_widgets_present(): void {
		$widgets = DashboardController::get_widgets_for_role( 'artist' );
		$this->assertNotEmpty( $widgets );
	}

	public function test_org_widgets_present(): void {
		$widgets = DashboardController::get_widgets_for_role( 'organization' );
		$this->assertNotEmpty( $widgets );
	}

	public function test_donor_has_no_widgets(): void {
		$widgets = DashboardController::get_widgets_for_role( 'donor' );
		$this->assertSame( array(), $widgets );
	}

	public function test_sponsor_has_no_widgets(): void {
		$widgets = DashboardController::get_widgets_for_role( 'sponsor' );
		$this->assertSame( array(), $widgets );
	}
}
