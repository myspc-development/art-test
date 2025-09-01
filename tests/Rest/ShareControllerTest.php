<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ShareController;
use ArtPulse\Core\EventMetrics;
use ArtPulse\Core\EventViewCounter;
use ArtPulse\Core\ProfileMetrics;

/**
 * @group REST
 */
class ShareControllerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		EventMetrics::install_table();
		ProfileMetrics::install_table();

		$this->event_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		$this->user_id  = self::factory()->user->create();

		ShareController::register();
		EventViewCounter::register();
		ProfileMetrics::register();
		do_action( 'rest_api_init' );
	}

	public function test_share_updates_event_and_profile_counts(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/share' );
		$req->set_param( 'object_id', $this->event_id );
		$req->set_param( 'object_type', 'artpulse_event' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( '1', get_post_meta( $this->event_id, 'share_count', true ) );

		$req2 = new \WP_REST_Request( 'POST', '/artpulse/v1/share' );
		$req2->set_param( 'object_id', $this->user_id );
		$req2->set_param( 'object_type', 'user' );
		$res2 = rest_get_server()->dispatch( $req2 );
		$this->assertSame( 200, $res2->get_status() );
		$this->assertSame( '1', get_user_meta( $this->user_id, 'share_count', true ) );
	}
}
