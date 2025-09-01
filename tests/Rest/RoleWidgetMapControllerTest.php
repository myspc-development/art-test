<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RoleWidgetMapController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group REST
 */
class RoleWidgetMapControllerTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();

		// Reset registry
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		// Ensure roles exist
		foreach ( array( 'member', 'artist', 'organization' ) as $role ) {
			if ( ! get_role( $role ) ) {
				add_role( $role, ucfirst( $role ) );
			}
		}

               DashboardWidgetRegistry::register(
                       'widget_alpha',
                       'Alpha',
                       '',
                       '',
                       '__return_null',
                       array( 'roles' => array( 'member' ) )
               );
               DashboardWidgetRegistry::register(
                       'widget_beta',
                       'Beta',
                       '',
                       '',
                       '__return_null',
                       array( 'roles' => array( 'artist' ) )
               );
               DashboardWidgetRegistry::register(
                       'widget_gamma',
                       'Gamma',
                       '',
                       '',
                       '__return_null'
               );

		RoleWidgetMapController::register();
		do_action( 'rest_api_init' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_get_role_widget_map(): void {
               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/role-widget-map' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 200, $res->get_status() );

               $this->assertSame(
                       array(
                               'member'       => array( 'widget_alpha', 'widget_gamma' ),
                               'artist'       => array( 'widget_beta', 'widget_gamma' ),
                               'organization' => array( 'widget_gamma' ),
                       ),
                       $res->get_data()
               );
       }
}
