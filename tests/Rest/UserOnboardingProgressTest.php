<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\UserDashboardManager;

/**
 * @group REST
 */
class UserOnboardingProgressTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );
		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_progress_marks_completed(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/onboarding' );
		$req->set_body_params( array( 'step' => 'profile' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( 'profile' ), get_user_meta( $this->user_id, 'ap_onboarding_steps', true ) );
		$this->assertSame( '1', get_user_meta( $this->user_id, 'ap_onboarding_completed', true ) );
	}
}
