<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group core

 */

class DashboardWidgetRegistryMapTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		foreach ( array( 'member', 'artist', 'organization' ) as $role ) {
			if ( ! get_role( $role ) ) {
				add_role( $role, ucfirst( $role ) );
			}
		}
	}

	public function test_get_role_widget_map_groups_widgets(): void {
               DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
               DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'artist' ) ) );
               DashboardWidgetRegistry::register( 'widget_gamma', 'Gamma', '', '', '__return_null' );

               $map        = DashboardWidgetRegistry::get_role_widget_map();
               $member_ids = wp_list_pluck( $map['member'], 'id' );
               $artist_ids = wp_list_pluck( $map['artist'], 'id' );

               $this->assertContains( 'widget_alpha', $member_ids );
               $this->assertNotContains( 'widget_beta', $member_ids );
               $this->assertContains( 'widget_beta', $artist_ids );
               $this->assertContains( 'widget_gamma', $member_ids );
               $this->assertContains( 'widget_gamma', $artist_ids );
	}
}
