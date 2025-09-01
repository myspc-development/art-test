<?php
namespace ArtPulse\DashboardBuilder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group core

 */

class DashboardBuilderRegistryGetForRoleTest extends TestCase {

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

	public function test_get_for_role_requires_explicit_match(): void {
               DashboardWidgetRegistry::register(
                       'widget_alpha',
                       array(
                               'title'           => 'Alpha',
                               'render_callback' => '__return_null',
                               'roles'           => array( 'member' ),
                       )
               );
               DashboardWidgetRegistry::register(
                       'widget_beta',
                       array(
                               'title'           => 'Beta',
                               'render_callback' => '__return_null',
                               'roles'           => array( 'artist' ),
                       )
               );
		DashboardWidgetRegistry::register(
			'unassigned',
			array(
				'title'           => 'Unassigned',
				'render_callback' => '__return_null',
			)
		);

               $member = DashboardWidgetRegistry::get_for_role( 'member' );
               $artist = DashboardWidgetRegistry::get_for_role( 'artist' );

               $this->assertArrayHasKey( 'widget_alpha', $member );
               $this->assertArrayNotHasKey( 'widget_beta', $member );
               $this->assertArrayNotHasKey( 'unassigned', $member );

               $this->assertArrayHasKey( 'widget_beta', $artist );
               $this->assertArrayNotHasKey( 'widget_alpha', $artist );
               $this->assertArrayNotHasKey( 'unassigned', $artist );
	}
}
