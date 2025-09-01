<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CurrentUserController;
use function ap_rest_request;
use function ap_as_user_with_role;

/**
 * @group REST
 */
class CurrentUserControllerTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
                // The plugin registers a custom `member` role which replaces the
                // default WordPress `subscriber` role. Ensure the test user uses
                // the correct role to align with the controller's response.
                $this->user_id = ap_as_user_with_role( 'member' );
                CurrentUserController::register();
                do_action( 'rest_api_init' );
	}

	public function test_requires_authentication(): void {
                wp_set_current_user( 0 );
                $res = ap_rest_request( 'GET', '/artpulse/v1/me' );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_returns_current_user_information(): void {
                wp_set_current_user( $this->user_id );
                $res = ap_rest_request( 'GET', '/artpulse/v1/me' );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
               $this->assertSame( $this->user_id, $data['id'] );
               // The primary role should reflect the custom `member` role.
               $this->assertSame( 'member', $data['role'] );
               $this->assertContains( 'member', $data['roles'] );
               $this->assertSame( $data['roles'][0], $data['role'] );
        }
}
