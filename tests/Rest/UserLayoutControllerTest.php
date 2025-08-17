<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\UserLayoutController;

/**
 * @group restapi
 */
class UserLayoutControllerTest extends \WP_UnitTestCase
{
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);
        UserLayoutController::register();
        do_action('rest_api_init');
    }

    public function test_guest_cannot_access(): void
    {
        wp_set_current_user(0);
        $req = new WP_REST_Request('GET', '/artpulse/v1/user/layout');
        $req->set_param('role', 'member');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());
    }

    public function test_post_saves_layout(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/user/layout');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params(['role' => 'member', 'layout' => ['a', 'b']]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['a', 'b'], get_user_meta($this->user_id, 'ap_layout_member', true));
    }
}

