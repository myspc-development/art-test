<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group core

 */

class DashboardWidgetRegistryCallbacksTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', 'Member' );
		}
		$uid = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $uid );
	}

	public function test_member_widgets_return_callable_callbacks(): void {
               DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
               DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', static function () {}, array( 'roles' => array( 'member' ) ) );

               $widgets = DashboardWidgetRegistry::get_widgets( 'member' );

		$this->assertNotEmpty( $widgets );
		foreach ( $widgets as $cb ) {
			$this->assertTrue( is_callable( $cb ) );
		}
	}
}
