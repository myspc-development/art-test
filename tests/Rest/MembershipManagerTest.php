<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\MembershipManager;


/**
 * @group REST
 */
class MembershipManagerTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );
		MembershipManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_get_returns_membership_data(): void {
		update_user_meta( $this->user_id, 'ap_membership_level', 'Gold' );
		update_user_meta( $this->user_id, 'ap_membership_expires', 1234567890 );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/user/membership' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'Gold', $data['level'] );
		$this->assertSame( 1234567890, $data['expires'] );
	}

	public function test_post_updates_membership_data(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/membership' );
		$req->set_param( 'level', 'Silver' );
		$req->set_param( 'expires', 111 );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'Silver', get_user_meta( $this->user_id, 'ap_membership_level', true ) );
	}
}
