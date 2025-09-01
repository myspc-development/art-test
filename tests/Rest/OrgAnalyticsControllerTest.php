<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\OrgAnalyticsController;
use ArtPulse\Rest\RsvpRestController;

/**
 * @group REST
 */
class OrgAnalyticsControllerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id  = self::factory()->user->create( array( 'role' => 'organization' ) );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_user_meta( $this->user_id, 'ap_organization_id', 5 );
		update_post_meta( $this->event_id, '_ap_event_organization', 5 );
		update_post_meta(
			$this->event_id,
			'event_rsvp_history',
			array(
				'2024-01-01' => 2,
				'2024-01-02' => 3,
			)
		);

		OrgAnalyticsController::register();
		RsvpRestController::register();
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->user_id );
	}

	public function test_get_event_rsvp_stats(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/rsvp-stats' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( array( '2024-01-01', '2024-01-02' ), $data['dates'] );
		$this->assertSame( array( 2, 3 ), $data['counts'] );
		$this->assertSame( 5, $data['total_rsvps'] );
	}
}
