<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DirectoryController;

/**
 * @group restapi
 */
class DirectoryControllerTest extends \WP_UnitTestCase {
    private int $near_event;
    private int $far_event;
    private int $past_event;
    private int $other_region_event;

    public function set_up(): void {
        parent::set_up();
        register_taxonomy( 'region', 'artpulse_event' );
        register_post_type( 'artpulse_event', array( 'public' => true, 'supports' => array( 'title' ) ) );

        $future = time() + DAY_IN_SECONDS;
        $past   = time() - DAY_IN_SECONDS;

        $this->near_event = wp_insert_post( array(
            'post_title'  => 'Near Future',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'meta_input'  => array(
                'event_lat'      => '40.70',
                'event_lng'      => '-74.00',
                'ap_event_end_ts'=> $future,
            ),
        ) );
        wp_set_post_terms( $this->near_event, 'brooklyn', 'region' );

        $this->far_event = wp_insert_post( array(
            'post_title'  => 'Far Future',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'meta_input'  => array(
                'event_lat'      => '42.00',
                'event_lng'      => '-75.00',
                'ap_event_end_ts'=> $future,
            ),
        ) );
        wp_set_post_terms( $this->far_event, 'brooklyn', 'region' );

        $this->past_event = wp_insert_post( array(
            'post_title'  => 'Past Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'meta_input'  => array(
                'event_lat'      => '40.70',
                'event_lng'      => '-74.00',
                'ap_event_end_ts'=> $past,
            ),
        ) );
        wp_set_post_terms( $this->past_event, 'brooklyn', 'region' );

        $this->other_region_event = wp_insert_post( array(
            'post_title'  => 'Other Region',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'meta_input'  => array(
                'event_lat'      => '40.70',
                'event_lng'      => '-74.00',
                'ap_event_end_ts'=> $future,
            ),
        ) );
        wp_set_post_terms( $this->other_region_event, 'manhattan', 'region' );

        DirectoryController::register();
        do_action( 'rest_api_init' );
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
