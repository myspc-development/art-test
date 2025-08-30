<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../../widgets/DonationsWidget.php';

use ArtPulse\Widgets\DonationsWidget;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use Brain\Monkey;
use Brain\Monkey\Functions;

if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
	define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
}

if ( ! function_exists( 'locate_template' ) ) {
	function locate_template( $path ) {
		return ''; }
}


if ( ! function_exists( 'load_template' ) ) {
	function load_template( $file, $require_once = false ) {
		echo 'template'; }
}

class DonationsWidgetTest extends WP_UnitTestCase {
        protected function setUp(): void {
                parent::setUp();
                Monkey\setUp();
                Functions\when( 'plugin_dir_path' )->alias( fn( $file ) => sys_get_temp_dir() . '/' );
                DashboardWidgetRegistry::reset();
                DonationsWidget::register();
        }

        protected function tearDown(): void {
                Monkey\tearDown();
                parent::tearDown();
        }

	public function test_registration_and_rendering(): void {
		$this->assertTrue( DashboardWidgetRegistry::exists( DonationsWidget::get_id() ) );

		MockStorage::$current_roles = array( 'organization' );
                $authorized                 = DonationsWidget::render( 1 );
                $this->assertStringContainsString( 'Example donations', $authorized );

		MockStorage::$current_roles = array( 'subscriber' );
		$denied                     = DonationsWidget::render( 2 );
		$this->assertStringContainsString( 'You do not have access', $denied );
	}
}
