<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group ADMIN
 */

class ListWidgetsForRoleTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		if ( $ref->hasProperty( 'builder_widgets' ) ) {
			$b = $ref->getProperty( 'builder_widgets' );
			$b->setAccessible( true );
			$b->setValue( null, array() );
		}
	}

	public function test_widget_without_callback_is_disabled(): void {
				DashboardWidgetRegistry::register(
					'widget_foo',
					array(
						'title'           => 'Foo',
						'render_callback' => '__return_null',
						'roles'           => array( 'administrator' ),
					)
				);
		DashboardWidgetRegistry::register(
			'bar',
			array(
				'title' => 'Bar',
				'roles' => array( 'administrator' ),
			)
		);

		$widgets = DashboardWidgetTools::listWidgetsForRole( 'administrator' );
		$map     = array();
		foreach ( $widgets as $w ) {
			$map[ $w['id'] ] = $w;
		}

		$this->assertArrayHasKey( 'bar', $map );
		$this->assertTrue( $map['bar']['disabled'] );
		$this->assertSame( 'no_renderer', $map['bar']['disabled_reason'] );
				$this->assertFalse( $map['widget_foo']['disabled'] );
	}
}
