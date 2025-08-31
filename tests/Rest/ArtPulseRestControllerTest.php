<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group restapi
 */
class ArtPulseRestControllerTest extends \WP_UnitTestCase {
	private int $user;
	private int $other_user;
	private int $event_id;

	public function set_up(): void {
		parent::set_up();
		$this->user       = self::factory()->user->create();
		$this->other_user = self::factory()->user->create();
		$this->event_id   = wp_insert_post(
			array(
				'post_title'  => 'Test Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		\ArtPulse_REST_Controller::register();
		do_action( 'rest_api_init' );
	}

	public function test_rsvp_event_and_cancel(): void {
		wp_set_current_user( $this->user );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/rsvp' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->event_id ), get_user_meta( $this->user, 'ap_rsvp_events', true ) );

		$req = new \WP_REST_Request( 'DELETE', '/artpulse/v1/event/' . $this->event_id . '/rsvp' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertEmpty( get_user_meta( $this->user, 'ap_rsvp_events', true ) );
	}

	public function test_rsvp_invalid_event(): void {
		wp_set_current_user( $this->user );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/999999/rsvp' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	public function test_rsvp_requires_auth(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/rsvp' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_follow_and_unfollow_user(): void {
		wp_set_current_user( $this->user );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/' . $this->other_user . '/follow' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->other_user ), get_user_meta( $this->user, 'ap_following', true ) );

		$req = new \WP_REST_Request( 'DELETE', '/artpulse/v1/user/' . $this->other_user . '/follow' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertEmpty( get_user_meta( $this->user, 'ap_following', true ) );
	}

	public function test_follow_invalid_user(): void {
		wp_set_current_user( $this->user );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/999999/follow' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 404, $res->get_status() );
	}

	public function test_follow_requires_auth(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/' . $this->other_user . '/follow' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}
}
