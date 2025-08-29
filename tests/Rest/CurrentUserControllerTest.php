<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CurrentUserController;

/**
 * @group restapi
 */
class CurrentUserControllerTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
                // The plugin registers a custom `member` role which replaces the
                // default WordPress `subscriber` role. Ensure the test user uses
                // the correct role to align with the controller's response.
                $this->user_id = self::factory()->user->create(
                        array(
                                'role' => 'member',
                        )
                );
		CurrentUserController::register();
		do_action( 'rest_api_init' );
	}

	public function test_requires_authentication(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/me' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_returns_current_user_information(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/me' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
               $this->assertSame( $this->user_id, $data['id'] );
               // The primary role should reflect the custom `member` role.
               $this->assertSame( 'member', $data['role'] );
               $this->assertContains( 'member', $data['roles'] );
               $this->assertSame( $data['roles'][0], $data['role'] );
        }
}
