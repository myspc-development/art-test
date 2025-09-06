<?php

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\DonationsWidget;
use Brain\Monkey\Functions;

if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
		define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
}

/**

 * @group WIDGETS
 */

class DonationsWidgetTest extends WidgetTestCase {
	protected function setUp(): void {
			parent::setUp();
			Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => sys_get_temp_dir() . '/' );
			Functions\when( 'locate_template' )->alias( fn( $path ) => '' );
			Functions\when( 'load_template' )->alias(
				function ( $file, $require_once = false ) {
					echo 'template';
				}
			);
			DashboardWidgetRegistry::reset();
			DonationsWidget::register();
	}

	public function test_registration_and_rendering(): void {
		$this->assertTrue( DashboardWidgetRegistry::exists( DonationsWidget::get_id() ) );

		MockStorage::$current_roles = array( 'organization' );
				$authorized         = DonationsWidget::render( 1 );
				$this->assertStringContainsString( 'Example donations', $authorized );

		MockStorage::$current_roles = array( 'subscriber' );
		$denied                     = DonationsWidget::render( 2 );
		$this->assertStringContainsString( 'You do not have access', $denied );
	}
}
