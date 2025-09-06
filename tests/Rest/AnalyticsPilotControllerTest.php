<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\AnalyticsPilotController;

/**
 * @group REST
 */
class AnalyticsPilotControllerTest extends \WP_UnitTestCase {

	private int $admin;
	private int $user;

	public function set_up() {
		parent::set_up();
		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->user  = self::factory()->user->create( array( 'user_email' => 'partner@example.com' ) );
		wp_set_current_user( $this->admin );
		AnalyticsPilotController::register();
		do_action( 'rest_api_init' );
	}

	public function test_invite_assigns_capability(): void {
			$req = new \WP_REST_Request( 'POST', '/' . ARTPULSE_API_NAMESPACE . '/analytics/pilot/invite' );
			$req->set_param( 'email', 'partner@example.com' );
			$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
			$res = rest_get_server()->dispatch( $req );
			$this->assertSame( 200, $res->get_status() );
			$this->assertTrue( user_can( $this->user, 'ap_analytics_pilot' ) );
	}
}
