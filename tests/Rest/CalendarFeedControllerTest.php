<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CalendarFeedController;
use WP_REST_Request;

/**
 * @group restapi
 */
class CalendarFeedControllerTest extends \WP_UnitTestCase
{
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        wp_set_current_user(self::factory()->user->create());
        $this->event_id = wp_insert_post([
            'post_title'  => 'Calendar Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->event_id, 'event_start_date', '2030-01-01');
        CalendarFeedController::register();
        do_action('rest_api_init');
    }

    public function test_feed_returns_event(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/calendar');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertCount(1, $data);
        $this->assertSame($this->event_id, $data[0]['id']);
    }
}
