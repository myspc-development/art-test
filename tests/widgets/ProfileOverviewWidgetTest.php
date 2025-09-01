<?php
require_once __DIR__ . '/bootstrap.php';

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\ProfileOverviewWidget;

/**

 * @group widgets

 */

class ProfileOverviewWidgetTest extends WidgetTestCase {
        protected function setUp(): void {
                parent::setUp();
                DashboardWidgetRegistry::reset();
                ProfileOverviewWidget::register();
        }

	public function test_registration_and_access(): void {
		$this->assertTrue( DashboardWidgetRegistry::exists( ProfileOverviewWidget::get_id() ) );

		MockStorage::$current_roles = array( 'artist' );
		$this->assertTrue( ProfileOverviewWidget::can_view( 1 ) );
		$output = ProfileOverviewWidget::render( 1 );
		$this->assertStringContainsString( 'Profile statistics coming soon', $output );

		MockStorage::$current_roles = array( 'subscriber' );
		$this->assertFalse( ProfileOverviewWidget::can_view( 2 ) );
		$denied = ProfileOverviewWidget::render( 2 );
		$this->assertStringContainsString( 'You do not have access', $denied );
	}
}
