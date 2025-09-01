<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RoleMatrixController;

/**
 * @group REST
 */
class RoleMatrixBatchTest extends \WP_UnitTestCase {
	private int $admin;
	private int $user;

	public function set_up() {
		parent::set_up();
		RoleMatrixController::register();
		do_action( 'rest_api_init' );
		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->user  = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $this->admin );
	}

	public function test_batch_endpoint_updates_roles(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/roles/batch' );
		$req->set_body_params(
			array(
				$this->user => array( 'organization' => true ),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$u = get_userdata( $this->user );
		$this->assertContains( 'organization', $u->roles );
	}
}
