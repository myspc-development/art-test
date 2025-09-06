<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DirectoryController;
use ArtPulse\Tests\RestFactory;
use ArtPulse\Tests\TimeMock;

/**
 * @group REST
 */
class DirectoryControllerTest extends \WP_UnitTestCase {
	private int $near_event;
	private int $far_event;
	private int $past_event;
	private int $other_region_event;

	public function set_up(): void {
		parent::set_up();
		TimeMock::freeze( strtotime( '2024-01-01 00:00:00' ) );
		register_taxonomy( 'region', 'artpulse_event' );
		register_post_type(
			'artpulse_event',
			array(
				'public'   => true,
				'supports' => array( 'title' ),
			)
		);

		$future = TimeMock::now() + DAY_IN_SECONDS;
		$past   = TimeMock::now() - DAY_IN_SECONDS;

		$this->near_event = RestFactory::event( array( 'post_title' => 'Near Future' ) );
		RestFactory::seed_event_meta(
			$this->near_event,
			array(
				'event_lat'       => '40.70',
				'event_lng'       => '-74.00',
				'ap_event_end_ts' => $future,
			)
		);
		wp_set_post_terms( $this->near_event, 'brooklyn', 'region' );

		$this->far_event = RestFactory::event( array( 'post_title' => 'Far Future' ) );
		RestFactory::seed_event_meta(
			$this->far_event,
			array(
				'event_lat'       => '42.00',
				'event_lng'       => '-75.00',
				'ap_event_end_ts' => $future,
			)
		);
		wp_set_post_terms( $this->far_event, 'brooklyn', 'region' );

		$this->past_event = RestFactory::event( array( 'post_title' => 'Past Event' ) );
		RestFactory::seed_event_meta(
			$this->past_event,
			array(
				'event_lat'       => '40.70',
				'event_lng'       => '-74.00',
				'ap_event_end_ts' => $past,
			)
		);
		wp_set_post_terms( $this->past_event, 'brooklyn', 'region' );

		$this->other_region_event = RestFactory::event( array( 'post_title' => 'Other Region' ) );
		RestFactory::seed_event_meta(
			$this->other_region_event,
			array(
				'event_lat'       => '40.70',
				'event_lng'       => '-74.00',
				'ap_event_end_ts' => $future,
			)
		);
		wp_set_post_terms( $this->other_region_event, 'manhattan', 'region' );

		DirectoryController::register();
		do_action( 'rest_api_init' );
	}

	public function tear_down(): void {
		TimeMock::unfreeze();
		parent::tear_down();
	}

	public function test_region_and_radius_filters_exclude_events(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'region', 'brooklyn' );
		$req->set_param( 'lat', 40.70 );
		$req->set_param( 'lng', -74.00 );
		$req->set_param( 'within_km', 50 );

		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->near_event, $ids );
		$this->assertNotContains( $this->far_event, $ids );
		$this->assertNotContains( $this->past_event, $ids );
		$this->assertNotContains( $this->other_region_event, $ids );
	}
}
