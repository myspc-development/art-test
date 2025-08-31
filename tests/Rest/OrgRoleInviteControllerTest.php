<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\OrgRoleInviteController;
use ArtPulse\Core\MultiOrgRoles;
use ArtPulse\Core\OrgInviteManager;

/**
 * @group restapi
 */
class OrgRoleInviteControllerTest extends \WP_UnitTestCase {

	private int $admin;
	private int $user;
	private int $org;

	public function set_up() {
		parent::set_up();
		MultiOrgRoles::install_table();
		OrgInviteManager::install_table();
		$this->org   = self::factory()->post->create( array( 'post_type' => 'artpulse_org' ) );
		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->user  = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		MultiOrgRoles::assign_roles( $this->admin, $this->org, array( 'admin' ) );
		OrgRoleInviteController::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->admin );
	}

	public function test_invite_and_accept(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/org-roles/invite' );
		$req->set_param( 'email', 'new@example.com' );
		$req->set_param( 'org_id', $this->org );
		$req->set_param( 'role', 'curator' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$token = $res->get_data()['token'];

		wp_set_current_user( $this->user );
		$req2 = new \WP_REST_Request( 'POST', '/artpulse/v1/org-roles/accept' );
		$req2->set_param( 'token', $token );
		$res2 = rest_get_server()->dispatch( $req2 );
		$this->assertSame( 200, $res2->get_status() );
		$roles = MultiOrgRoles::get_user_roles( $this->user, $this->org );
		$this->assertContains( 'curator', $roles );
	}
}
