<?php

use ArtPulse\Rest\PortfolioRestController;

/**
 * @group REST
 */
class PortfolioPermissionsTest extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		PortfolioRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_requires_authentication() {
		wp_set_current_user( 0 );
		$request  = new \WP_REST_Request( 'GET', '/ap/v1/portfolio' );
		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();
                $this->assertSame( 403, $status ); // 403: unauthenticated requests are forbidden
	}

	public function test_allows_authenticated_user() {
		$user_id = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $user_id );
		$request  = new \WP_REST_Request( 'GET', '/ap/v1/portfolio' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
	}
}
