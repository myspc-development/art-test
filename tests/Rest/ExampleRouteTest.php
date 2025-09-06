<?php
require_once __DIR__ . '/RouteTestCase.php';

/**
 * @group REST
 */
class ExampleRouteTest extends RouteTestCase {
	public function test_users_me_route() {
		$response = $this->req( 'GET', '/wp/v2/users/me' );
		$this->assertSame( 401, $response->get_status() );

		$user_id  = self::factory()->user->create();
		$response = $this->req( 'GET', '/wp/v2/users/me', array(), $user_id );
		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
	}
}
