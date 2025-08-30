<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RestRoutes;

/**
 * @group restapi
 */
class AdvancedEventFiltersTest extends \WP_UnitTestCase {

	private int $event1;
	private int $event2;

	public function set_up() {
		parent::set_up();
		$date = date( 'Y-m-d', strtotime( '+1 day' ) );

                register_taxonomy( 'genre', 'artpulse_event' );
                register_taxonomy( 'medium', 'artpulse_event' );

                $this->event1 = wp_insert_post(
                        array(
                                'post_title'  => 'Painting Show',
                                'post_type'   => 'artpulse_event',
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                        'event_start_date'     => $date,
                                        'event_end_date'       => $date,
                                        'event_lat'            => '48.86',
                                        'event_lng'            => '2.35',
                                        'event_street_address' => '123 Road',
                                ),
                        )
                );
                wp_set_object_terms( $this->event1, array( 'painting' ), 'genre' );
                wp_set_object_terms( $this->event1, array( 'oil' ), 'medium' );
                update_post_meta( $this->event1, 'vibe', 'quiet' );
                update_post_meta( $this->event1, 'accessibility', array( 'wheelchair' ) );
                update_post_meta( $this->event1, 'age_range', 'adults' );

                $this->event2 = wp_insert_post(
                        array(
                                'post_title'  => 'Video Demo',
                                'post_type'   => 'artpulse_event',
                                'post_status' => 'publish',
                                'meta_input'  => array(
                                        'event_start_date'     => $date,
                                        'event_end_date'       => $date,
                                        'event_lat'            => '34.05',
                                        'event_lng'            => '-118.25',
                                        'event_street_address' => '456 Street',
                                ),
                        )
                );
                wp_set_object_terms( $this->event2, array( 'installation' ), 'genre' );
                wp_set_object_terms( $this->event2, array( 'video' ), 'medium' );
                update_post_meta( $this->event2, 'vibe', 'loud' );
                update_post_meta( $this->event2, 'accessibility', array( 'asl' ) );
                update_post_meta( $this->event2, 'age_range', 'kids' );

		RestRoutes::register();
		do_action( 'rest_api_init' );
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
		$evt = $data[0];
		$this->assertArrayHasKey( 'event_lat', $evt );
		$this->assertArrayHasKey( 'event_lng', $evt );
		$this->assertArrayHasKey( 'event_start_date', $evt );
		$this->assertArrayHasKey( 'event_street_address', $evt );
	}
}
