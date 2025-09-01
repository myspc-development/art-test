<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\NearbyEventsController;

/**
 * @group REST
 */
class NearbyEventsControllerTest extends \WP_UnitTestCase {

	private int $near_event;
	private int $far_event;

	public function set_up() {
		parent::set_up();
		$future           = date( 'Y-m-d', strtotime( '+1 day' ) );
		$this->near_event = wp_insert_post(
			array(
				'post_title'  => 'Near Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_lat'        => '40.70',
					'event_lng'        => '-74.00',
					'event_start_date' => $future,
					'event_end_date'   => $future,
				),
			)
		);
		$this->far_event  = wp_insert_post(
			array(
				'post_title'  => 'Far Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_lat'        => '42.00',
					'event_lng'        => '-75.00',
					'event_start_date' => $future,
					'event_end_date'   => $future,
				),
			)
		);
		NearbyEventsController::register();
		do_action( 'rest_api_init' );
	}

	public function test_returns_events_within_radius(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events/nearby' );
		$req->set_param( 'lat', 40.70 );
		$req->set_param( 'lng', -74.00 );
		$req->set_param( 'radius', 50 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->near_event, $ids );
		$this->assertNotContains( $this->far_event, $ids );
	}

	public function test_missing_parameters_return_error(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events/nearby' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
	}
}
