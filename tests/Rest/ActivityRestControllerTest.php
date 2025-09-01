<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ActivityRestController;
use ArtPulse\Core\ActivityLogger;

/**
 * @group REST
 */
class ActivityRestControllerTest extends \WP_UnitTestCase {

	private int $uid;

	public function set_up() {
		parent::set_up();
		$this->uid = self::factory()->user->create();
		wp_set_current_user( $this->uid );
		ActivityLogger::install_table();
		ActivityRestController::register();
		do_action( 'rest_api_init' );
		ActivityLogger::log( null, $this->uid, 'login', 'User logged in' );
	}

	public function test_activity_endpoint_returns_rows(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/activity' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertNotEmpty( $data );
		$this->assertSame( 'login', $data[0]->action_type );
	}
}
