<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\OrgRolesController;

/**
 * @group REST
 */
class OrgRolesMatrixRestTest extends \WP_UnitTestCase {

	private int $admin;
	private int $user;
	private int $org;

	public function set_up() {
		parent::set_up();
		$this->org   = self::factory()->post->create( array( 'post_type' => 'artpulse_org' ) );
		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->user  = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		update_user_meta( $this->admin, 'ap_organization_id', $this->org );
		update_user_meta( $this->user, 'ap_organization_id', $this->org );

		OrgRolesController::register();
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->admin );
	}

	public function tear_down() {
		$_GET = array();
		parent::tear_down();
	}

	public function test_get_roles_for_org_returns_data(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/org-roles' );
		$req->set_param( 'org_id', $this->org );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( $this->org, $data['org_id'] );
		$this->assertNotEmpty( $data['roles'] );
		$this->assertCount( 2, $data['users'] );
	}

	public function test_update_roles_assigns_role(): void {
		$nonce            = wp_create_nonce( 'wp_rest' );
		$_GET['_wpnonce'] = $nonce;
		$req              = new \WP_REST_Request( 'POST', '/artpulse/v1/org-roles/update' );
		$req->set_body_params(
			array(
				'org_id' => $this->org,
				'roles'  => array( $this->user => 'admin' ),
			)
		);
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'admin', get_user_meta( $this->user, 'ap_org_role', true ) );
	}
}
