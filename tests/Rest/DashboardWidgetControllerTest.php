<?php
namespace ArtPulse\Rest\Tests;


use ArtPulse\Rest\DashboardWidgetController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

/**
 * @group restapi
 */
class DashboardWidgetControllerTest extends \WP_UnitTestCase
{
    private int $uid;

    public function set_up()
    {
        parent::set_up();
        // Reset registry
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        if ($ref->hasProperty('builder_widgets')) {
            $b = $ref->getProperty('builder_widgets');
            $b->setAccessible(true);
            $b->setValue(null, []);
        }

        $this->uid = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($this->uid);

        DashboardWidgetRegistry::register('foo', [
            'title' => 'Foo',
            'render_callback' => '__return_null',
            'roles' => ['administrator'],
        ]);
        DashboardWidgetRegistry::register('bar', [
            'title' => 'Bar',
            'render_callback' => '__return_null',
            'roles' => ['editor'],
        ]);
        DashboardWidgetRegistry::register('baz', [
            'title' => 'Baz',
            'render_callback' => '__return_null',
        ]);

        DashboardWidgetController::register();
        do_action('rest_api_init');
    }

    public function test_get_widgets_for_role_only(): void
    {
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets');
        $req->set_param('role', 'administrator');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $ids  = array_column($data['available'], 'id');
        sort($ids);
        $this->assertSame(['baz', 'foo'], $ids);
        $this->assertArrayNotHasKey('all', $data);
    }

    public function test_get_widgets_with_all_list(): void
    {
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets');
        $req->set_param('role', 'administrator');
        $req->set_param('include_all', 'true');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $res = rest_get_server()->dispatch($req);

        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertArrayHasKey('all', $data);
        $all_ids = array_column($data['all'], 'id');
        sort($all_ids);
        $this->assertSame(['bar', 'baz', 'foo'], $all_ids);
    }

    public function test_save_layout_with_extra_widgets(): void
    {
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [
                ['id' => 'foo', 'visible' => true],
                ['id' => 'bar', 'visible' => false],
            ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());

        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertEquals([
            ['id' => 'foo', 'visible' => true],
            ['id' => 'bar', 'visible' => false],
        ], $saved['layout']);
        $this->assertSame([], $saved['logs']);
    }

    public function test_save_layout_requires_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }

    public function test_save_layout_rejects_invalid_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
        $req->set_header('X-WP-Nonce', 'badnonce');
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }

    public function test_save_layout_requires_manage_options_cap(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $subscriber = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }

    public function test_export_layout_endpoint(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets/export');
        $req->set_param('role', 'administrator');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $data = $res->get_data();
        $this->assertSame([['id' => 'foo', 'visible' => true]], $data['layout']);
    }

    public function test_export_layout_requires_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets/export');
        $req->set_param('role', 'administrator');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }

    public function test_export_layout_rejects_invalid_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets/export');
        $req->set_param('role', 'administrator');
        $req->set_header('X-WP-Nonce', 'badnonce');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }

    public function test_export_layout_requires_manage_options_cap(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $subscriber = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber);
        $req = new \WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets/export');
        $req->set_param('role', 'administrator');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
    }

    public function test_import_layout_endpoint(): void
    {
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/import');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'baz', 'visible' => true]], $saved['layout']);
        $this->assertSame([], $saved['logs']);
    }

    public function test_import_layout_requires_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/import');
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }

    public function test_import_layout_rejects_invalid_nonce(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/import');
        $req->set_header('X-WP-Nonce', 'badnonce');
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }

    public function test_import_layout_requires_manage_options_cap(): void
    {
        UserLayoutManager::save_role_layout('administrator', [ ['id' => 'foo', 'visible' => true] ]);
        $subscriber = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber);
        $req = new \WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/import');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([
            'role' => 'administrator',
            'layout' => [ ['id' => 'baz', 'visible' => true] ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(403, $res->get_status());
        $saved = UserLayoutManager::get_role_layout('administrator');
        $this->assertSame([['id' => 'foo', 'visible' => true]], $saved['layout']);
    }
}
