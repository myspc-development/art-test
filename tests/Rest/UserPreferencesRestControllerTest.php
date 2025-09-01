<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Community\UserPreferencesRestController;

/**
 * @group REST
 */
class UserPreferencesRestControllerTest extends \WP_UnitTestCase {

	private int $uid;

	public function set_up() {
		parent::set_up();
		$this->uid = self::factory()->user->create();
		wp_set_current_user( $this->uid );
		UserPreferencesRestController::register();
		do_action( 'init' );
		do_action( 'rest_api_init' );
	}

	public function test_preferences_saved(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user-preferences' );
		$req->set_body_params(
			array(
				'notification_prefs' => array(
					'email' => false,
					'push'  => true,
					'sms'   => false,
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$prefs = get_user_meta( $this->uid, 'ap_notification_prefs', true );
		$this->assertSame(
			array(
				'email' => false,
				'push'  => true,
				'sms'   => false,
			),
			$prefs
		);
	}
}
