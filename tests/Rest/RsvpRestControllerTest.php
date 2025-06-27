<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\RsvpRestController;

/**
 * @group restapi
 */
class RsvpRestControllerTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $user1;
    private int $user2;

    public function set_up(): void
    {
        parent::set_up();
        $this->user1 = self::factory()->user->create();
        $this->user2 = self::factory()->user->create();

        $this->event_id = wp_insert_post([
            'post_title'  => 'Test Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->event_id, 'event_rsvp_limit', 1);
        update_post_meta($this->event_id, 'event_rsvp_list', []);
        update_post_meta($this->event_id, 'event_waitlist', []);

        RsvpRestController::register();
        do_action('rest_api_init');
    }

    public function test_join_adds_user_to_rsvp_list(): void
    {
        wp_set_current_user($this->user1);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp');
        $req->set_param('event_id', $this->event_id);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame([$this->user1], get_post_meta($this->event_id, 'event_rsvp_list', true));
        $this->assertEmpty(get_post_meta($this->event_id, 'event_waitlist', true));
    }

    public function test_join_when_full_adds_to_waitlist(): void
    {
        update_post_meta($this->event_id, 'event_rsvp_list', [$this->user1]);
        wp_set_current_user($this->user2);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp');
        $req->set_param('event_id', $this->event_id);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame([$this->user1], get_post_meta($this->event_id, 'event_rsvp_list', true));
        $this->assertSame([$this->user2], get_post_meta($this->event_id, 'event_waitlist', true));
    }

    public function test_cancel_promotes_waitlisted_user(): void
    {
        update_post_meta($this->event_id, 'event_rsvp_list', [$this->user1]);
        update_post_meta($this->event_id, 'event_waitlist', [$this->user2]);
        wp_set_current_user($this->user1);
        $req = new WP_REST_Request('POST', '/artpulse/v1/rsvp/cancel');
        $req->set_param('event_id', $this->event_id);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame([$this->user2], get_post_meta($this->event_id, 'event_rsvp_list', true));
        $this->assertEmpty(get_post_meta($this->event_id, 'event_waitlist', true));
    }

    public function test_remove_from_waitlist(): void
    {
        update_post_meta($this->event_id, 'event_waitlist', [$this->user1]);
        wp_set_current_user($this->user1);
        $req = new WP_REST_Request('POST', '/artpulse/v1/waitlist/remove');
        $req->set_param('event_id', $this->event_id);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertEmpty(get_post_meta($this->event_id, 'event_waitlist', true));
    }
}
