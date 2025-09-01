<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CalendarFeedController;


/**
 * @group REST
 */
class CalendarFeedControllerTest extends \WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create() );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Calendar Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_start_date' => '2030-01-01',
					'event_end_date'   => '2030-01-02',
					'event_city'       => 'Los Angeles',
					'event_lat'        => '10.0',
					'event_lng'        => '20.0',
				),
			)
		);

		$org = wp_insert_post(
			array(
				'post_title'  => 'My Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'meta_input'  => array(
					'ead_org_street_address' => '123 Main',
				),
			)
		);
		update_post_meta( $this->event_id, '_ap_event_organization', $org );

		CalendarFeedController::register();
		do_action( 'rest_api_init' );
	}

	public function test_feed_returns_event(): void {
		$req = new \WP_REST_Request( 'GET', '/ap/v1/calendar' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$evt = $data[0];
		$this->assertSame( $this->event_id, $evt['id'] );
		$this->assertSame( 'Los Angeles', $evt['event_city'] );
		$this->assertSame( '123 Main', $evt['organization']['address'] );
		$this->assertArrayHasKey( 'meta', $evt );
		$this->assertSame( '2030-01-01', $evt['meta']['event_start_date'] );
	}
}
