<?php
namespace ArtPulse\Rest\Tests;


use ArtPulse\Rest\DashboardConfigController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class DashboardConfigControllerTest extends \WP_UnitTestCase
{
    private int $admin_id;
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->admin_id = self::factory()->user->create(['role' => 'administrator']);
        $this->user_id  = self::factory()->user->create(['role' => 'subscriber']);
        DashboardWidgetRegistry::register('one', 'One', '', '', '__return_null');
        DashboardWidgetRegistry::register('two', 'Two', '', '', '__return_null');
        DashboardConfigController::register();
        do_action('rest_api_init');
    }

    public function test_get_requires_read_capability(): void
    {
        wp_set_current_user(0);
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-config');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());

        wp_set_current_user($this->user_id);
        update_option('artpulse_widget_roles', ['subscriber' => ['one']]);
        update_option('artpulse_dashboard_layouts', ['subscriber' => ['one', 'two']]);
        update_option('artpulse_locked_widgets', ['two']);

        $req2 = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-config');
        $res2 = rest_get_server()->dispatch($req2);
        $this->assertSame(200, $res2->get_status());
        $data = $res2->get_data();
        $this->assertSame(['subscriber' => ['one']], $data['widget_roles']);
        $this->assertSame(['subscriber' => ['one', 'two']], $data['role_widgets']);
        $this->assertSame(['two'], $data['locked']);
    }

    public function test_post_requires_manage_options_and_valid_nonce(): void
    {
        wp_set_current_user($this->user_id);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-config');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([]);
        $req->set_header('Content-Type', 'application/json');
        $req->set_body(json_encode([
            'widget_roles' => ['subscriber' => ['one']],
        ]));
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(401, $res->get_status());

        wp_set_current_user($this->admin_id);
        $bad = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-config');
        $bad->set_body_params([]);
        $bad->set_header('Content-Type', 'application/json');
        $bad->set_header('X-WP-Nonce', 'badnonce');
        $bad->set_body(json_encode([
            'widget_roles' => ['administrator' => ['one']],
            'role_widgets' => ['administrator' => ['one', 'two']],
            'locked'       => ['two'],
        ]));
        $res_bad = rest_get_server()->dispatch($bad);
        $this->assertSame(403, $res_bad->get_status());

        $good = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-config');
        $good->set_body_params([]);
        $good->set_header('Content-Type', 'application/json');
        $good->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $good->set_body(json_encode([
            'widget_roles' => ['administrator' => ['one']],
            'role_widgets' => ['administrator' => ['one', 'two']],
            'locked'       => ['two'],
        ]));
        $res_good = rest_get_server()->dispatch($good);
        $this->assertSame(200, $res_good->get_status());
        $this->assertSame(['administrator' => ['one']], get_option('artpulse_widget_roles'));
        $this->assertSame(['administrator' => ['one', 'two']], get_option('artpulse_dashboard_layouts'));
        $this->assertSame(['two'], get_option('artpulse_locked_widgets'));
    }
}
