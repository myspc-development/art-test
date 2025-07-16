<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\ShareController;
use ArtPulse\Core\EventViewCounter;
use ArtPulse\Core\EventMetrics;
use ArtPulse\Core\ProfileMetrics;

/**
 * @group restapi
 */
class ShareControllerTest extends \WP_UnitTestCase
{
    private int $event_id;
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();

        // Install necessary data tables
        EventMetrics::install_table();
        ProfileMetrics::install_table();

        // Register all related systems
        EventViewCounter::register();
        ProfileMetrics::register();
        ShareController::register();
        do_action('rest_api_init');

        // Create test content
        $this->event_id = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);

        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);
    }

    public function test_share_logs_action_and_increments_count(): void
    {
        $fired = [];
        add_action('ap_event_shared', function ($id, $net) use (&$fired) {
            $fired[] = [$id, $net];
        }, 10, 2);

        $req = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req->set_param('object_id', $this->event_id);
        $req->set_param('object_type', 'artpulse_event');
        $req->set_param('network', 'facebook');

        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $this->assertSame([[$this->event_id, 'facebook']], $fired);
        $this->assertSame('1', get_post_meta($this->event_id, 'share_count', true));
    }

    public function test_requires_login(): void
    {
        wp_set_current_user(0);

        $req = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req->set_param('object_id', $this->event_id);
        $req->set_param('object_type', 'artpulse_event');

        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());
    }

    public function test_missing_params_returns_error(): void
    {
        $req1 = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req1->set_param('object_type', 'artpulse_event');
        $res1 = rest_get_server()->dispatch($req1);
        $this->assertSame(400, $res1->get_status());

        $req2 = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req2->set_param('object_id', $this->event_id);
        $res2 = rest_get_server()->dispatch($req2);
        $this->assertSame(400, $res2->get_status());
    }

    public function test_share_updates_event_and_profile_counts(): void
    {
        // Share an event
        $req1 = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req1->set_param('object_id', $this->event_id);
        $req1->set_param('object_type', 'artpulse_event');
        $res1 = rest_get_server()->dispatch($req1);
        $this->assertSame(200, $res1->get_status());
        $this->assertSame('1', get_post_meta($this->event_id, 'share_count', true));

        // Share a user profile
        $req2 = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req2->set_param('object_id', $this->user_id);
        $req2->set_param('object_type', 'user');
        $res2 = rest_get_server()->dispatch($req2);
        $this->assertSame(200, $res2->get_status());
        $this->assertSame('1', get_user_meta($this->user_id, 'share_count', true));
    }
}
