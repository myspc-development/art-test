<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Core\UserDashboardManager;

/**
 * @group restapi
 */
class DashboardLayoutTest extends \WP_UnitTestCase
{
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);
        UserDashboardManager::register();
        do_action('rest_api_init');
    }

    public function test_get_returns_layout_and_visibility(): void
    {
        update_user_meta($this->user_id, 'ap_dashboard_layout', ['one', 'two']);
        update_user_meta($this->user_id, 'ap_widget_visibility', ['one' => true]);
        $req = new WP_REST_Request('GET', '/artpulse/v1/ap_dashboard_layout');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame(['one', 'two'], $data['layout']);
        $this->assertSame(['one' => true], $data['visibility']);
    }

    public function test_post_saves_layout_and_visibility(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/ap_dashboard_layout');
        $req->set_body_params([
            'layout' => ['a', 'b', 'c'],
            'visibility' => ['a' => false, 'b' => true],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['a', 'b', 'c'], get_user_meta($this->user_id, 'ap_dashboard_layout', true));
        $this->assertSame(['a' => false, 'b' => true], get_user_meta($this->user_id, 'ap_widget_visibility', true));
    }
}

