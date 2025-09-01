<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group REST
 */
class SystemStatusEndpointTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		do_action( 'rest_api_init' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_status_returns_versions(): void {
               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/system-status' );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 200, $res->get_status() );
               $data = $res->get_data();
               $this->assertArrayHasKey( 'plugin', $data );
               $this->assertArrayHasKey( 'wordpress', $data );
       }
}
