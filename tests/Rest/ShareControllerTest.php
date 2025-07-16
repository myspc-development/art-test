<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\ShareController;
use ArtPulse\Core\EventViewCounter;
use ArtPulse\Core\EventMetrics;

/**
 * @group restapi
 */
class ShareControllerTest extends \WP_UnitTestCase
{
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        EventMetrics::install_table();
        EventViewCounter::register();
        ShareController::register();
        do_action('rest_api_init');

        $this->event_id = self::factory()->post->create([
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        wp_set_current_user(self::factory()->user->create());
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
        $res = rest_get_server()->dispatch($req1);
        $this->assertSame(400, $res->get_status());

        $req2 = new WP_REST_Request('POST', '/artpulse/v1/share');
        $req2->set_param('object_id', $this->event_id);
        $res = rest_get_server()->dispatch($req2);
        $this->assertSame(400, $res->get_status());
    }
}
