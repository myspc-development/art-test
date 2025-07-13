<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;

/**
 * @group restapi
 */
class ManagementEndpointsTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        do_action('init');
        do_action('rest_api_init');
        $this->event_id = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        $this->user_id = self::factory()->user->create();
    }

    public function test_attendees_requires_edit_permission(): void
    {
        wp_set_current_user($this->user_id);
        $req = new WP_REST_Request('GET', '/artpulse/v1/event/' . $this->event_id . '/attendees');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }

    public function test_comments_require_login(): void
    {
        wp_set_current_user(0);
        $req = new WP_REST_Request('GET', '/artpulse/v1/event/' . $this->event_id . '/comments');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }
}
