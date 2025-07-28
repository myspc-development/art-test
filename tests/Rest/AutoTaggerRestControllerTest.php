<?php
namespace ArtPulse\AI\Tests;

use WP_REST_Request;
use ArtPulse\AI\AutoTaggerRestController;

/**
 * @group restapi
 */
class AutoTaggerRestControllerTest extends \WP_UnitTestCase
{
    private int $admin;
    private int $subscriber;

    public function set_up(): void
    {
        parent::set_up();
        $this->admin = self::factory()->user->create(['role' => 'administrator']);
        $this->subscriber = self::factory()->user->create(['role' => 'subscriber']);

        AutoTaggerRestController::register();
        do_action('rest_api_init');
    }

    /**
     * Ensure the /tag endpoint returns an array of tags for POST requests.
     */
    public function test_endpoint_returns_tags(): void
    {
        wp_set_current_user($this->admin);
        $req = new WP_REST_Request('POST', '/artpulse/v1/tag');
        $req->set_param('text', 'art');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['abstract', 'modern'], $res->get_data());
    }

    /**
     * Input is sanitized and empty values return an error.
     */
    public function test_invalid_text_returns_error(): void
    {
        wp_set_current_user($this->admin);
        $req = new WP_REST_Request('POST', '/artpulse/v1/tag');
        $req->set_param('text', '   ');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(400, $res->get_status());
    }

    /**
     * Subscribers without edit_posts capability cannot access the endpoint.
     */
    public function test_requires_edit_posts_capability(): void
    {
        wp_set_current_user($this->subscriber);
        $req = new WP_REST_Request('POST', '/artpulse/v1/tag');
        $req->set_param('text', 'art');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }
}
