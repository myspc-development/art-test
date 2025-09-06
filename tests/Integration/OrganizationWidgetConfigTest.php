<?php
namespace ArtPulse\Integration\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\WidgetRegistryLoader;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group INTEGRATION
 */

class OrganizationWidgetConfigTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();

		// reset registry and loader caches
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		$loaderRef  = new \ReflectionClass( WidgetRegistryLoader::class );
		$configProp = $loaderRef->getProperty( 'config' );
		$configProp->setAccessible( true );
		$configProp->setValue( null, array() );
		$regProp = $loaderRef->getProperty( 'registered' );
		$regProp->setAccessible( true );
		$regProp->setValue( null, false );

		WidgetRegistryLoader::register_widgets();
		if ( ! get_role( 'organization' ) ) {
			add_role( 'organization', 'Organization' );
		}
		$uid = self::factory()->user->create( array( 'role' => 'organization' ) );
		wp_set_current_user( $uid );
	}

	public function test_org_widgets_registered_and_renderable(): void {
		$ids = array(
			'audience_crm',
			'sponsored_event_config',
			'embed_tool',
			'org_event_overview',
			'org_team_roster',
		);

		foreach ( $ids as $id ) {
			$this->assertNotNull(
				DashboardWidgetRegistry::get_widget( $id ),
				"$id not registered"
			);

			ob_start();
			ap_render_widget( $id );
			$html = ob_get_clean();
			$this->assertStringContainsString( $id, $html );
		}
	}
}
