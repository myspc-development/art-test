<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RestRoutes;
use ArtPulse\Tests\RestFactory;
use ArtPulse\Tests\TimeMock;

/**
 * @group REST
 */
class AdvancedEventFiltersTest extends \WP_UnitTestCase {

	private int $event1;
	private int $event2;

	public function set_up() {
			parent::set_up();
			TimeMock::freeze( strtotime( '2024-01-01 00:00:00' ) );
			$date = TimeMock::wp_date( 'Y-m-d', TimeMock::now() + DAY_IN_SECONDS );

			register_taxonomy( 'genre', 'artpulse_event' );
			register_taxonomy( 'medium', 'artpulse_event' );

			$this->event1 = RestFactory::event( array( 'post_title' => 'Painting Show' ) );
			RestFactory::seed_event_meta(
				$this->event1,
				array(
					'event_start_date'     => $date,
					'event_end_date'       => $date,
					'event_lat'            => '48.86',
					'event_lng'            => '2.35',
					'event_street_address' => '123 Road',
				)
			);
			wp_set_object_terms( $this->event1, array( 'painting' ), 'genre' );
			wp_set_object_terms( $this->event1, array( 'oil' ), 'medium' );
			update_post_meta( $this->event1, 'style', 'quiet' );
			update_post_meta( $this->event1, 'accessibility', array( 'wheelchair' ) );
			update_post_meta( $this->event1, 'age_range', 'adults' );

			$this->event2 = RestFactory::event( array( 'post_title' => 'Video Demo' ) );
			RestFactory::seed_event_meta(
				$this->event2,
				array(
					'event_start_date'     => $date,
					'event_end_date'       => $date,
					'event_lat'            => '34.05',
					'event_lng'            => '-118.25',
					'event_street_address' => '456 Street',
				)
			);
			wp_set_object_terms( $this->event2, array( 'installation' ), 'genre' );
			wp_set_object_terms( $this->event2, array( 'video' ), 'medium' );
			update_post_meta( $this->event2, 'style', 'loud' );
			update_post_meta( $this->event2, 'accessibility', array( 'asl' ) );
			update_post_meta( $this->event2, 'age_range', 'kids' );

			RestRoutes::register();
			do_action( 'rest_api_init' );
	}

	public function tear_down() {
			TimeMock::unfreeze();
			parent::tear_down();
	}

	public function test_filter_by_genre_and_medium(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'genre', array( 'painting' ) );
		$req->set_param( 'medium', array( 'oil' ) );
		$res = rest_get_server()->dispatch( $req );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->event1, $ids );
		$this->assertNotContains( $this->event2, $ids );
	}

	public function test_within_km_includes_required_fields(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'lat', 48.86 );
		$req->set_param( 'lng', 2.35 );
		$req->set_param( 'within_km', 5 );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$ids  = wp_list_pluck( $data, 'id' );
		$this->assertContains( $this->event1, $ids );
		$this->assertNotContains( $this->event2, $ids );
				$evt      = $data[0];
				$required = array(
					'id',
					'title',
					'link',
					'distance_km',
					'lat',
					'lng',
					'start_date',
					'end_date',
					'venue',
					'city',
					'state',
					'country',
					'thumbnail',
					'categories',
					'medium',
					'style',
				);
				foreach ( $required as $key ) {
						$this->assertArrayHasKey( $key, $evt );
				}
	}
}
