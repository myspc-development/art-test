<?php
namespace ArtPulse\Rest\Tests;


use ArtPulse\Rest\AnalyticsPilotController;

class AnalyticsPilotControllerTest extends \WP_UnitTestCase
{
    private int $admin;
    private int $user;

    public function set_up()
    {
        parent::set_up();
        $this->admin = self::factory()->user->create(['role' => 'administrator']);
        $this->user  = self::factory()->user->create(['user_email' => 'partner@example.com']);
        wp_set_current_user($this->admin);
        AnalyticsPilotController::register();
        do_action('rest_api_init');
    }

    public function test_invite_assigns_capability(): void
    {
        $req = new \WP_REST_Request('POST', '/artpulse/v1/analytics/pilot/invite');
        $req->set_param('email', 'partner@example.com');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertTrue(user_can($this->user, 'ap_premium_member'));
    }
}
