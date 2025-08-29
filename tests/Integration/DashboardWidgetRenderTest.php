<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRenderTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

               DashboardWidgetRegistry::register(
                       'widget_alpha',
                       'Alpha',
                       '',
                       '',
                       function () {
                               return 'alpha';
                       },
                       array( 'roles' => array( 'member' ) )
               );
               DashboardWidgetRegistry::register(
                       'widget_beta',
                       'Beta',
                       '',
                       '',
                       function () {
                               return 'beta';
                       },
                       array( 'roles' => array( 'artist' ) )
               );
               DashboardWidgetRegistry::register(
                       'widget_gamma',
                       'Gamma',
                       '',
                       '',
                       function () {
                               return 'gamma';
                       },
                       array( 'roles' => array( 'organization' ) )
               );
	}

	public static function roleProvider(): iterable {
               yield 'member' => array( 'member', array( 'widget_alpha' ) );
               yield 'artist' => array( 'artist', array( 'widget_beta' ) );
               yield 'organization' => array( 'organization', array( 'widget_gamma' ) );
	}

	/**
	 * @dataProvider roleProvider
	 */
	public function test_render_for_role( string $role, array $expected ): void {
		$uid = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $uid );
		ob_start();
		DashboardWidgetRegistry::render_for_role( $uid );
		$html = ob_get_clean();
               foreach ( $expected as $id ) {
                       $this->assertStringContainsString( $id, $html );
               }
               foreach ( array_diff( array( 'widget_alpha', 'widget_beta', 'widget_gamma' ), $expected ) as $other ) {
                       $this->assertStringNotContainsString( $other, $html );
               }
	}
}
