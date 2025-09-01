<?php
namespace ArtPulse\DashboardBuilder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetVisibility;

/**

 * @group CORE

 */

class DashboardBuilderRegistryVisibilityTest extends TestCase {

	protected function setUp(): void {
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		if ( $ref->hasProperty( 'builder_widgets' ) ) {
			$bw = $ref->getProperty( 'builder_widgets' );
			$bw->setAccessible( true );
			$bw->setValue( null, array() );
		}
	}

	public function test_default_visibility_is_public(): void {
               DashboardWidgetRegistry::register(
                       'widget_alpha',
                       array(
                               'title'           => 'Alpha',
                               'render_callback' => '__return_null',
                       )
               );

               $all = DashboardWidgetRegistry::get_all( null, true );
               $this->assertSame( WidgetVisibility::PUBLIC, $all['widget_alpha']['visibility'] );
	}

	public function test_filter_by_visibility(): void {
		DashboardWidgetRegistry::register(
			'a',
			array(
				'title'           => 'A',
				'render_callback' => '__return_null',
				'visibility'      => WidgetVisibility::PUBLIC,
			)
		);
		DashboardWidgetRegistry::register(
			'b',
			array(
				'title'           => 'B',
				'render_callback' => '__return_null',
				'visibility'      => WidgetVisibility::INTERNAL,
			)
		);
		DashboardWidgetRegistry::register(
			'c',
			array(
				'title'           => 'C',
				'render_callback' => '__return_null',
				'visibility'      => WidgetVisibility::DEPRECATED,
			)
		);

		$public   = DashboardWidgetRegistry::get_all( WidgetVisibility::PUBLIC, true );
		$internal = DashboardWidgetRegistry::get_all( WidgetVisibility::INTERNAL, true );

		$this->assertArrayHasKey( 'a', $public );
		$this->assertArrayNotHasKey( 'b', $public );
		$this->assertArrayHasKey( 'b', $internal );
		$this->assertArrayNotHasKey( 'c', $internal );
	}
}
