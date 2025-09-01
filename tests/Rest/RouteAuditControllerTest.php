<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group REST
 */
class RouteAuditControllerTest extends \WP_UnitTestCase {
        public function set_up(): void {
                parent::set_up();
                \ArtPulse\Rest\RouteAudit::register();
                do_action( 'rest_api_init' );
        }

        public function test_subscriber_can_access_audit(): void {
                wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
                $req = new \WP_REST_Request( 'GET', '/ap/v1/routes/audit' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 200, $res->get_status() );
                $data = $res->get_data();
                $this->assertArrayHasKey( 'routes', $data );
                $this->assertIsArray( $data['routes'] );
                $this->assertNotEmpty( $data['routes'] );
                $route = $data['routes'][0];
                $this->assertArrayHasKey( 'path', $route );
                $this->assertArrayHasKey( 'methods', $route );
                $this->assertArrayHasKey( 'callback', $route );
                $this->assertArrayHasKey( 'conflicts', $data );
                $this->assertTrue( is_array( $data['conflicts'] ) || $data['conflicts'] === null );
        }

       public function test_requires_authentication(): void {
               wp_set_current_user( 0 );
               $req = new \WP_REST_Request( 'GET', '/ap/v1/routes/audit' );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 401, $res->get_status() );
       }
}
