<?php
/**
 * @group restapi
 */
class UserLayoutControllerTest extends WP_UnitTestCase {
	protected $user_id;

	public function set_up() {
		parent::set_up();
		set_error_handler( fn() => true, E_USER_WARNING );
		$this->user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		restore_error_handler();
		wp_set_current_user( $this->user_id );
		global $wp_rest_server;
		$wp_rest_server = $wp_rest_server ?: rest_get_server();
		do_action( 'rest_api_init' );
	}

	public function test_get_layout_defaults_to_preset_when_empty() {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/user/layout' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$response = rest_do_request( $req );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'layout', $data );
		$this->assertIsArray( $data['layout'] );
	}

	public function test_post_layout_persists_for_user() {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/layout' );
		$req->set_body_params(
			array(
				'role'   => 'artist',
				'layout' => array( 'upcomingEvents', 'sales', 'tasks' ),
			)
		);
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

		$response = rest_do_request( $req );
		$this->assertSame( 200, $response->get_status(), print_r( $response->get_data(), true ) );
		$this->assertTrue( $response->get_data()['saved'] );

		// Fetch again to confirm persistence
		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/user/layout' );
		$get->set_query_params( array( 'role' => 'artist' ) );
		$get->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$get = rest_do_request( $get );
		$this->assertSame( 200, $get->get_status() );
		$this->assertSame( array( 'upcomingevents', 'sales', 'tasks' ), $get->get_data()['layout'] );
	}
}
