<?php
namespace ArtPulse\AI\Tests;

use WP_REST_Request;
use ArtPulse\AI\AutoTaggerRestController;
use function add_filter;

/**
 * @group restapi
 */
class AutoTaggerRestControllerTest extends \WP_UnitTestCase
{
    private int $admin;
    private int $subscriber;
    private string $mock_body = '';

    public function set_up(): void
    {
        parent::set_up();
        $this->admin = self::factory()->user->create(['role' => 'administrator']);
        $this->subscriber = self::factory()->user->create(['role' => 'subscriber']);

        AutoTaggerRestController::register();
        do_action('rest_api_init');

        update_option('openai_api_key', 'test');
        add_filter('pre_http_request', [$this, 'mock_request'], 10, 3);
    }

    public function tear_down(): void
    {
        remove_filter('pre_http_request', [$this, 'mock_request'], 10);
        parent::tear_down();
    }

    public function mock_request($pre, $args, $url)
    {
        if (str_contains($url, 'api.openai.com')) {
            return [
                'headers'  => [],
                'response' => ['code' => 200],
                'body'     => $this->mock_body,
            ];
        }
        return false;
    }

    /**
     * Ensure the /tag endpoint returns an array of tags for POST requests.
     */
    public function test_endpoint_returns_tags(): void
    {
        wp_set_current_user($this->admin);
        $this->mock_body = json_encode(['choices' => [ ['message' => ['content' => 'abstract, modern']] ]]);
        $req = new WP_REST_Request('POST', '/artpulse/v1/tag');
        $req->set_param('text', 'art');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['tags' => ['abstract', 'modern']], $res->get_data());
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
