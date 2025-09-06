<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Rest\RsvpRestController;
use ArtPulse\Community\NotificationManager;
use ArtPulse\Community\NotificationHooks;

/**

 * @group COMMUNITY
 */

class RsvpNotificationTest extends \WP_UnitTestCase {

	private int $organizer_id;
	private int $user_id;
	private int $event_id;

	public function set_up() {
		parent::set_up();
		NotificationManager::install_notifications_table();

		RsvpRestController::register();
		NotificationHooks::register();
		do_action( 'rest_api_init' );

		$this->organizer_id = self::factory()->user->create( array( 'role' => 'organization' ) );
		$this->user_id      = self::factory()->user->create();

		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Test Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->organizer_id,
			)
		);
		update_post_meta( $this->event_id, 'event_rsvp_enabled', '1' );
		update_post_meta( $this->event_id, 'event_rsvp_limit', 0 );
		update_post_meta( $this->event_id, 'event_rsvp_list', array() );
		update_post_meta( $this->event_id, 'event_waitlist', array() );
	}

	public function test_rsvp_creates_notification_for_organizer(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_notifications';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, type FROM $table WHERE user_id = %d", $this->organizer_id ), ARRAY_A );

		$this->assertNotEmpty( $row );
		$this->assertSame( 'rsvp_received', $row['type'] );
	}
}
