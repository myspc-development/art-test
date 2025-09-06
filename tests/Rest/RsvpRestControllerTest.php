<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RsvpRestController;
use ArtPulse\Tests\Email;

/**
 * @group REST
 */
class RsvpRestControllerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $user1;
	private int $user2;
	private int $user3;

	public static function setUpBeforeClass(): void {
			parent::setUpBeforeClass();
			Email::install();
	}

	public function set_up() {
			parent::set_up();
			$this->user1 = self::factory()->user->create( array( 'role' => 'organization' ) );
			$this->user2 = self::factory()->user->create();
			$this->user3 = self::factory()->user->create( array( 'role' => 'organization' ) );

		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Test Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
				'post_author' => $this->user1,
			)
		);
		update_post_meta( $this->event_id, 'event_rsvp_enabled', '1' );
		update_post_meta( $this->event_id, 'event_rsvp_limit', 1 );
		update_post_meta( $this->event_id, '_ap_event_organization', 99 );
		update_user_meta( $this->user1, 'ap_organization_id', 99 );
		update_user_meta( $this->user3, 'ap_organization_id', 99 );
		update_post_meta( $this->event_id, 'event_rsvp_list', array() );
		update_post_meta( $this->event_id, 'event_waitlist', array() );

		$u1 = new \WP_User( $this->user1 );
		$u1->add_cap( 'edit_artpulse_event' );
		$u1->add_cap( 'edit_artpulse_events' );

		RsvpRestController::register();
		do_action( 'rest_api_init' );
	}

	public function tear_down() {
			Email::clear();
			parent::tear_down();
	}

	public function test_join_adds_user_to_rsvp_list(): void {
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->user1 ), get_post_meta( $this->event_id, 'event_rsvp_list', true ) );
		$this->assertEmpty( get_post_meta( $this->event_id, 'event_waitlist', true ) );
		$this->assertSame( array( $this->event_id ), get_user_meta( $this->user1, 'ap_rsvp_events', true ) );
	}

	public function test_join_when_full_adds_to_waitlist(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		wp_set_current_user( $this->user2 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->user1 ), get_post_meta( $this->event_id, 'event_rsvp_list', true ) );
		$this->assertSame( array( $this->user2 ), get_post_meta( $this->event_id, 'event_waitlist', true ) );
		$this->assertEmpty( get_user_meta( $this->user2, 'ap_rsvp_events', true ) );
	}

	public function test_cancel_promotes_waitlisted_user(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		update_post_meta( $this->event_id, 'event_waitlist', array( $this->user2 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp/cancel' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->user2 ), get_post_meta( $this->event_id, 'event_rsvp_list', true ) );
		$this->assertEmpty( get_post_meta( $this->event_id, 'event_waitlist', true ) );
		$this->assertEmpty( get_user_meta( $this->user1, 'ap_rsvp_events', true ) );
	}

	public function test_remove_from_waitlist(): void {
		update_post_meta( $this->event_id, 'event_waitlist', array( $this->user1 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/waitlist/remove' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertEmpty( get_post_meta( $this->event_id, 'event_waitlist', true ) );
	}

	public function test_join_stores_event_in_user_meta(): void {
		wp_set_current_user( $this->user2 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		rest_get_server()->dispatch( $req );
		$this->assertSame( array( $this->event_id ), get_user_meta( $this->user2, 'ap_rsvp_events', true ) );
	}

	public function test_cancel_removes_event_from_user_meta(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		update_user_meta( $this->user1, 'ap_rsvp_events', array( $this->event_id ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp/cancel' );
		$req->set_param( 'event_id', $this->event_id );
		rest_get_server()->dispatch( $req );
		$this->assertEmpty( get_user_meta( $this->user1, 'ap_rsvp_events', true ) );
	}

	public function test_get_attendees_returns_lists(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		update_post_meta( $this->event_id, 'event_waitlist', array( $this->user2 ) );
		update_post_meta( $this->event_id, 'event_attended', array( $this->user1 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/attendees' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data['attendees'] );
		$this->assertSame( 'confirmed', $data['attendees'][0]['status'] );
		$this->assertCount( 1, $data['waitlist'] );
	}

	public function test_toggle_attended(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/attendees/' . $this->user1 . '/attended' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'user_id', $this->user1 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->user1 ), get_post_meta( $this->event_id, 'event_attended', true ) );
	}

	public function test_remove_attendee_promotes_waitlist(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		update_post_meta( $this->event_id, 'event_waitlist', array( $this->user2 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/attendees/' . $this->user1 . '/remove' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'user_id', $this->user1 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( $this->user2 ), get_post_meta( $this->event_id, 'event_rsvp_list', true ) );
		$this->assertEmpty( get_post_meta( $this->event_id, 'event_waitlist', true ) );
	}

	public function test_join_sends_confirmation_and_org_email(): void {
		update_post_meta( $this->event_id, 'event_organizer_email', 'org@test.com' );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		rest_get_server()->dispatch( $req );
				$this->assertCount( 2, Email::messages() );
	}

	public function test_bulk_email_rsvps(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1, $this->user2 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/email-rsvps' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'subject', 'Hi' );
		$req->set_param( 'message', 'Hello' );
		rest_get_server()->dispatch( $req );
				$this->assertCount( 2, Email::messages() );
	}

	public function test_email_single_attendee(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event_id . '/attendees/' . $this->user1 . '/message' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'user_id', $this->user1 );
		$req->set_param( 'subject', 'Hi' );
		$req->set_param( 'message', 'Hello' );
		rest_get_server()->dispatch( $req );
				$this->assertCount( 1, Email::messages() );
	}

	public function test_join_fails_when_rsvp_disabled(): void {
		update_post_meta( $this->event_id, 'event_rsvp_enabled', '0' );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	public function test_join_requires_login(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_attendee_list_requires_edit_permission(): void {
		wp_set_current_user( $this->user3 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/attendees' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_export_attendees_csv(): void {
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->user1 ) );
		update_post_meta(
			$this->event_id,
			'event_rsvp_data',
			array(
				$this->user1 => array( 'date' => '2024-01-01 10:00:00' ),
			)
		);
		update_post_meta( $this->event_id, 'event_attended', array( $this->user1 ) );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/attendees/export' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$csv = $res->get_data();
		$this->assertStringContainsString( 'Name,Email,Status,RSVP Date,Attended', $csv );
		$user = get_userdata( $this->user1 );
		$this->assertStringContainsString( $user->user_email, $csv );
	}
}
