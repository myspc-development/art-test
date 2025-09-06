<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\FollowVenueCuratorController;

/**
 * @group REST
 */
class FollowVenueCuratorControllerTest extends \WP_UnitTestCase {

	private int $user;
	private int $venue;
	private int $curator;

	public function set_up() {
		parent::set_up();
		$this->user = self::factory()->user->create();
		wp_set_current_user( $this->user );
		$this->venue   = wp_insert_post(
			array(
				'post_title'  => 'Venue',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
			)
		);
		$this->curator = self::factory()->user->create( array( 'role' => 'curator' ) );

		FollowVenueCuratorController::register();
		do_action( 'rest_api_init' );
	}

	public function test_follow_endpoints(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/follow/venue' );
		$req->set_param( 'venue_id', $this->venue );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->venue ), get_user_meta( $this->user, 'ap_following_venues', true ) );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/follow/curator' );
		$req->set_param( 'curator_id', $this->curator );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->curator ), get_user_meta( $this->user, 'ap_following_curators', true ) );

				$req = new \WP_REST_Request( 'GET', '/artpulse/v1/followed/venues' );
				$res = rest_get_server()->dispatch( $req );
				$this->assertSame( 200, $res->get_status() );
				$this->assertContains( $this->venue, $res->get_data() );

				$req = new \WP_REST_Request( 'GET', '/artpulse/v1/followed/curators' );
				$res = rest_get_server()->dispatch( $req );
				$this->assertSame( 200, $res->get_status() );
				$this->assertContains( $this->curator, $res->get_data() );
	}
}
