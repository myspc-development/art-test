<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Core\DashboardWidgetRegistry;

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
        DashboardWidgetRegistry::register('one', 'one', '', '', '__return_null');
        DashboardWidgetRegistry::register('two', 'two', '', '', '__return_null');
        DashboardWidgetRegistry::register('a', 'a', '', '', '__return_null');
        DashboardWidgetRegistry::register('b', 'b', '', '', '__return_null');
        DashboardWidgetRegistry::register('c', 'c', '', '', '__return_null');
        DashboardWidgetRegistry::register('a-', 'a-', '', '', '__return_null');
        DashboardWidgetRegistry::register('bc', 'bc', '', '', '__return_null');
        DashboardWidgetRegistry::register('invalidslug', 'invalid', '', '', '__return_null');
        do_action('rest_api_init');
    }

    public function test_get_returns_layout_and_visibility(): void
    {
        update_user_meta($this->user_id, 'ap_dashboard_layout', [
            ['id' => 'one', 'visible' => true],
            ['id' => 'two', 'visible' => true],
        ]);
        update_user_meta($this->user_id, 'ap_widget_visibility', ['one' => true]);
        $req = new WP_REST_Request('GET', '/artpulse/v1/ap/layout');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame(['one', 'two'], $data['layout']);
        $this->assertSame(['one' => true, 'two' => true], $data['visibility']);
    }

    public function test_post_saves_layout_and_visibility(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/ap/layout/save');
        $req->set_body_params([
            'layout' => [
                ['id' => 'a', 'visible' => false],
                ['id' => 'b', 'visible' => true],
                ['id' => 'c', 'visible' => true]
            ]
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $expected = [
            ['id' => 'a', 'visible' => false],
            ['id' => 'b', 'visible' => true],
            ['id' => 'c', 'visible' => true],
        ];
        $this->assertSame($expected, get_user_meta($this->user_id, 'ap_dashboard_layout', true));
    }

    public function test_post_sanitizes_layout_values(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/ap/layout/save');
        $req->set_body_params([
            'layout' => [
                ['id' => 'A-'],
                ['id' => 'B C'],
                ['id' => 'in valid/slug']
            ]
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $expected = [
            ['id' => 'a-', 'visible' => true],
            ['id' => 'bc', 'visible' => true],
            ['id' => 'invalidslug', 'visible' => true]
        ];
        $this->assertSame($expected, get_user_meta($this->user_id, 'ap_dashboard_layout', true));
    }

    public function test_post_ignores_duplicates_and_invalid_ids(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/ap/layout/save');
        $req->set_body_params([
            'layout' => [
                ['id' => 'a'],
                ['id' => 'b'],
                ['id' => 'a'],
                ['id' => 'invalid']
            ]
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $expected = [
            ['id' => 'a', 'visible' => true],
            ['id' => 'b', 'visible' => true]
        ];
        $this->assertSame($expected, get_user_meta($this->user_id, 'ap_dashboard_layout', true));
    }

    public function test_get_uses_role_default_when_no_user_meta(): void
    {
        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        update_option('ap_dashboard_widget_config', ['member' => ['membership', 'upgrade']]);

        $req = new WP_REST_Request('GET', '/artpulse/v1/ap/layout');
        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame(['membership', 'upgrade'], $data['layout']);
        $this->assertSame(['membership' => true, 'upgrade' => true], $data['visibility']);
    }

    public function test_get_sanitizes_layout_values(): void
    {
        update_user_meta($this->user_id, 'ap_dashboard_layout', [
            ['id' => 'A-'],
            ['id' => 'B C'],
            ['id' => 'in valid/slug']
        ]);
        $req = new WP_REST_Request('GET', '/artpulse/v1/ap/layout');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $expected = ['a-', 'bc', 'invalidslug'];
        $this->assertSame($expected, $res->get_data()['layout']);
    }
    public function test_get_alias_route_returns_data(): void
    {
        update_user_meta($this->user_id, 'ap_dashboard_layout', [
            ['id' => 'one', 'visible' => true],
            ['id' => 'two', 'visible' => false],
        ]);
        $req = new WP_REST_Request('GET', '/artpulse/v1/dashboard/layout');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame(['one', 'two'], $data['layout']);
    }

    public function test_post_alias_route_saves_layout(): void
    {
        $req = new WP_REST_Request('POST', '/artpulse/v1/dashboard/layout');
        $req->set_body_params([
            'layout' => [
                ['id' => 'a', 'visible' => false],
                ['id' => 'b', 'visible' => true],
            ]
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $expected = [
            ['id' => 'a', 'visible' => false],
            ['id' => 'b', 'visible' => true],
        ];
        $this->assertSame($expected, get_user_meta($this->user_id, 'ap_dashboard_layout', true));
    }
}
