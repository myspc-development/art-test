<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CurrentUserController;
use function ArtPulse\Rest\Tests\as_role;
use function ArtPulse\Rest\Tests\call;
use function ArtPulse\Rest\Tests\assertStatus;
use function ArtPulse\Rest\Tests\body;

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
                $this->user_id = as_role( 'member' );
                CurrentUserController::register();
                do_action( 'rest_api_init' );
	}

	public function test_requires_authentication(): void {
                wp_set_current_user( 0 );
                $res = call( 'GET', '/artpulse/v1/me' );
                assertStatus( $res, 401 );
	}

	public function test_returns_current_user_information(): void {
                wp_set_current_user( $this->user_id );
                $res  = call( 'GET', '/artpulse/v1/me' );
                assertStatus( $res, 200 );
                $data = body( $res );
                $this->assertSame( $this->user_id, $data['id'] );
               // The primary role should reflect the custom `member` role.
               $this->assertSame( 'member', $data['role'] );
               $this->assertContains( 'member', $data['roles'] );
               $this->assertSame( $data['roles'][0], $data['role'] );
        }
}
