<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\UserDashboardManager;

class ArtistProfileUpdateTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $this->user_id );
		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_profile_rest_update_persists(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/profile' );
		$req->set_body_params(
			array(
				'display_name' => 'New Name',
				'ap_country'   => 'US',
				'ap_state'     => 'NY',
				'ap_city'      => 'New York',
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$user = get_userdata( $this->user_id );
		$this->assertSame( 'New Name', $user->display_name );
		$this->assertSame( 'US', get_user_meta( $this->user_id, 'ap_country', true ) );
		$this->assertSame( 'NY', get_user_meta( $this->user_id, 'ap_state', true ) );
		$this->assertSame( 'New York', get_user_meta( $this->user_id, 'ap_city', true ) );
	}
}
