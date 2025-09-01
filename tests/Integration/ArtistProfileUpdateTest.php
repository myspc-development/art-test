<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\UserDashboardManager;

/**

 * @group integration

 */

class ArtistProfileUpdateTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();

		// Create & authenticate a user for the request context.
		$this->user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $this->user_id );

		// Ensure plugin-side dashboard/widgets init and REST routes are registered.
		UserDashboardManager::register();
		do_action( 'rest_api_init' ); // make sure routes are present
	}

	public function tear_down() {
		// Reset REST server so routes/state don't leak between tests.
		global $wp_rest_server;
		$wp_rest_server = null;

		// Reset current user for safety.
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	public function test_profile_rest_update_persists(): void {
		// NOTE: Ensure your plugin registers this exact route:
		// namespace: 'artpulse/v1', route: '/user/profile', method: POST.
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/profile' );
		$req->set_body_params(
			[
				'display_name' => 'New Name',
				'ap_country'   => 'US',
				'ap_state'     => 'NY',
				'ap_city'      => 'New York',
			]
		);

		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status(), 'Profile endpoint did not return 200' );

		// Fresh read of the user and meta.
		clean_user_cache( $this->user_id );
		$user = get_userdata( $this->user_id );

		$this->assertSame( 'New Name', $user->display_name );
		$this->assertSame( 'US', get_user_meta( $this->user_id, 'ap_country', true ) );
		$this->assertSame( 'NY', get_user_meta( $this->user_id, 'ap_state', true ) );
		$this->assertSame( 'New York', get_user_meta( $this->user_id, 'ap_city', true ) );
	}
}
