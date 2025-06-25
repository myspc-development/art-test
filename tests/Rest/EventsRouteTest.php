<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\RestRoutes;

/**
 * @group restapi
 */
class EventsRouteTest extends \WP_UnitTestCase
{
    private int $la_event;
    private int $ny_event;

    public function set_up(): void
    {
        parent::set_up();

        $this->la_event = wp_insert_post([
            'post_title'  => 'LA Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->la_event, 'event_city', 'Los Angeles');
        update_post_meta($this->la_event, 'event_state', 'CA');
        update_post_meta($this->la_event, '_ap_event_date', '2024-01-01');
        update_post_meta($this->la_event, '_ap_event_location', 'LA');

        $this->ny_event = wp_insert_post([
            'post_title'  => 'NY Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->ny_event, 'event_city', 'New York City');
        update_post_meta($this->ny_event, 'event_state', 'NY');
        update_post_meta($this->ny_event, '_ap_event_date', '2024-01-01');
        update_post_meta($this->ny_event, '_ap_event_location', 'NY');

        RestRoutes::register();
        do_action('rest_api_init');
    }

    public function test_query_by_coordinates_returns_nearest_city_events(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/events');
        $req->set_param('lat', 34.05);
        $req->set_param('lng', -118.25);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $ids = wp_list_pluck($res->get_data(), 'id');
        $this->assertContains($this->la_event, $ids);
        $this->assertNotContains($this->ny_event, $ids);
    }

    public function test_coordinates_do_not_override_region_filter(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/events');
        $req->set_param('lat', 34.05);
        $req->set_param('lng', -118.25);
        $req->set_param('region', 'NY');
        $res = rest_get_server()->dispatch($req);
        $ids = wp_list_pluck($res->get_data(), 'id');
        $this->assertContains($this->ny_event, $ids);
        $this->assertNotContains($this->la_event, $ids);
    }
}
