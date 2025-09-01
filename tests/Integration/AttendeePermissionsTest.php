<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Rest\RsvpRestController;

/**

 * @group INTEGRATION

 */

class AttendeePermissionsTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $owner;
	private int $other;

	public function set_up() {
		parent::set_up();
		do_action( 'init' );

		$this->owner = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->other = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Test Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->owner,
			)
		);
		update_post_meta( $this->event_id, 'event_rsvp_list', array( $this->owner ) );

		RsvpRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_unauthorized_user_cannot_view_attendees(): void {
		wp_set_current_user( $this->other );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/attendees' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}
}
