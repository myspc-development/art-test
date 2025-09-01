<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RestRoutes;

/**
 * @group REST
 */
class EventsRouteTest extends \WP_UnitTestCase {

	private int $la_event;
	private int $ny_event;
	private int $past_event;

	public function set_up() {
		parent::set_up();

		$future1 = date( 'Y-m-d', strtotime( '+1 day' ) );
		$future2 = date( 'Y-m-d', strtotime( '+2 days' ) );

		$this->la_event = wp_insert_post(
			array(
				'post_title'  => 'LA Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_city'           => 'Los Angeles',
					'event_state'          => 'CA',
					'event_start_date'     => $future1,
					'event_end_date'       => $future1,
					'venue_name'           => 'LA Venue',
					'event_street_address' => '123 Main St',
					'event_postcode'       => '90001',
					'event_country'        => 'US',
					'event_lat'            => '34.05',
					'event_lng'            => '-118.25',
					'_ap_event_date'       => $future1,
					'_ap_event_location'   => 'LA',
				),
			)
		);

		$this->ny_event = wp_insert_post(
			array(
				'post_title'  => 'NY Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_city'           => 'New York City',
					'event_state'          => 'NY',
					'event_start_date'     => $future2,
					'event_end_date'       => $future2,
					'venue_name'           => 'NY Venue',
					'event_street_address' => '456 Broadway',
					'event_postcode'       => '10001',
					'event_country'        => 'US',
					'event_lat'            => '40.71',
					'event_lng'            => '-74.00',
					'_ap_event_date'       => $future2,
					'_ap_event_location'   => 'NY',
				),
			)
		);

		$this->past_event = wp_insert_post(
			array(
				'post_title'  => 'Past Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'event_city'         => 'Los Angeles',
					'event_state'        => 'CA',
					'event_start_date'   => date( 'Y-m-d', strtotime( '-1 day' ) ),
					'event_end_date'     => date( 'Y-m-d', strtotime( '-1 day' ) ),
					'venue_name'         => 'Old Venue',
					'_ap_event_date'     => date( 'Y-m-d', strtotime( '-1 day' ) ),
					'_ap_event_location' => 'LA',
				),
			)
		);

		RestRoutes::register();
		do_action( 'rest_api_init' );
	}

	public function test_query_by_coordinates_returns_events_within_radius(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'lat', 34.05 );
		$req->set_param( 'lng', -118.25 );
		$req->set_param( 'radius', 0.5 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->la_event, $ids );
		$this->assertNotContains( $this->ny_event, $ids );
	}

	public function test_coordinates_do_not_override_region_filter(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'lat', 34.05 );
		$req->set_param( 'lng', -118.25 );
		$req->set_param( 'region', 'NY' );
		$res = rest_get_server()->dispatch( $req );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->ny_event, $ids );
		$this->assertNotContains( $this->la_event, $ids );
	}

	public function test_event_response_includes_meta_and_org(): void {
		$org = wp_insert_post(
			array(
				'post_title'  => 'My Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'meta_input'  => array(
					'ead_org_street_address'        => '123',
					'ead_org_website_url'           => 'http://example.com',
					'ead_org_primary_contact_name'  => 'Alice',
					'ead_org_primary_contact_email' => 'a@example.com',
					'ead_org_primary_contact_phone' => '555',
					'ead_org_primary_contact_role'  => 'Lead',
				),
			)
		);

		update_post_meta( $this->la_event, '_ap_event_organization', $org );
		update_post_meta( $this->la_event, 'event_rsvp_enabled', '1' );
		update_post_meta( $this->la_event, 'event_rsvp_limit', 5 );
		update_post_meta( $this->la_event, 'event_waitlist_enabled', '1' );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$req->set_param( 'city', 'Los Angeles' );
		$req->set_param( 'region', 'CA' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$event = $data[0];
		$this->assertSame( '1', $event['rsvp_enabled'] );
		$this->assertSame( '5', $event['rsvp_limit'] );
		$this->assertSame( '1', $event['waitlist_enabled'] );
		$future1 = date( 'Y-m-d', strtotime( '+1 day' ) );
		$this->assertSame( $future1, $event['event_start_date'] );
		$this->assertSame( $future1, $event['event_end_date'] );
		$this->assertSame( 'LA Venue', $event['venue_name'] );
		$this->assertSame( '123 Main St', $event['event_street_address'] );
		$this->assertSame( 'Los Angeles', $event['event_city'] );
		$this->assertSame( 'CA', $event['event_state'] );
		$this->assertSame( '90001', $event['event_postcode'] );
		$this->assertSame( 'US', $event['event_country'] );
		$this->assertSame( '34.05', $event['event_lat'] );
		$this->assertSame( '-118.25', $event['event_lng'] );
		$this->assertSame( $org, intval( $event['event_organization'] ) );
		$this->assertIsArray( $event['organization'] );
		$this->assertSame( 'My Org', $event['organization']['name'] );
		$this->assertSame( '123', $event['organization']['address'] );
		$this->assertSame( 'http://example.com', $event['organization']['website'] );
		$this->assertSame( 'Alice', $event['organization']['contact_name'] );
		$this->assertSame( 'a@example.com', $event['organization']['contact_email'] );
		$this->assertSame( '555', $event['organization']['contact_phone'] );
		$this->assertSame( 'Lead', $event['organization']['contact_role'] );
	}

	public function test_past_events_are_excluded(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/events' );
		$res = rest_get_server()->dispatch( $req );
		$ids = wp_list_pluck( $res->get_data(), 'id' );
		$this->assertContains( $this->la_event, $ids );
		$this->assertContains( $this->ny_event, $ids );
		$this->assertNotContains( $this->past_event, $ids );
	}
}
