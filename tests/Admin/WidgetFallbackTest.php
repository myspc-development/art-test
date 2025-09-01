<?php
namespace ArtPulse\Admin\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group ADMIN

 */

class WidgetFallbackTest extends WP_UnitTestCase {

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

	public function test_missing_callback_outputs_fallback(): void {
               DashboardWidgetRegistry::register( 'widget_foo', 'Foo', '', '', 'missing_func' );
               $cb = DashboardWidgetRegistry::get_widget_callback( 'widget_foo' );
		ob_start();
		if ( $cb ) {
			$cb();
		}
		$html = ob_get_clean();
		$this->assertStringContainsString( 'Widget callback is missing', $html );
	}
}
