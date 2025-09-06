<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group REST
 */
class WidgetEditorControllerTest extends \WP_UnitTestCase {
	public function set_up(): void {
			parent::set_up();
			\ArtPulse\Rest\WidgetEditorController::register();
			do_action( 'rest_api_init' );
	}

	public function test_roles_endpoint_allows_read_users(): void {
			wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
			$req = new \WP_REST_Request( 'GET', '/artpulse/v1/roles' );
			$res = rest_get_server()->dispatch( $req );
			$this->assertSame( 200, $res->get_status() );
	}

	public function test_widgets_endpoint_filters_by_role(): void {
			// Reset registry
			$ref  = new \ReflectionClass( \ArtPulse\Core\DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );

			// Ensure roles exist
		foreach ( array( 'member', 'artist' ) as $role ) {
			if ( ! get_role( $role ) ) {
				add_role( $role, ucfirst( $role ) );
			}
		}

			\ArtPulse\Core\DashboardWidgetRegistry::register(
				'widget_alpha',
				'Alpha',
				'',
				'',
				'__return_null',
				array( 'roles' => array( 'member' ) )
			);
			\ArtPulse\Core\DashboardWidgetRegistry::register(
				'widget_beta',
				'Beta',
				'',
				'',
				'__return_null',
				array( 'roles' => array( 'artist' ) )
			);
			\ArtPulse\Core\DashboardWidgetRegistry::register(
				'widget_gamma',
				'Gamma',
				'',
				'',
				'__return_null'
			);

			wp_set_current_user( self::factory()->user->create( array( 'role' => 'member' ) ) );

			$req = new \WP_REST_Request( 'GET', '/artpulse/v1/widgets' );
			$req->set_param( 'role', 'member' );
			$res = rest_get_server()->dispatch( $req );
			$this->assertSame( 200, $res->get_status() );
			$ids = wp_list_pluck( $res->get_data(), 'id' );
			sort( $ids );
			$this->assertSame( array( 'widget_alpha', 'widget_gamma' ), $ids );
	}
}
