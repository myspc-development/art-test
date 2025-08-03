<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\DashboardWidgetController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class DashboardWidgetControllerTest extends \WP_UnitTestCase
{
    private int $uid;

    public function set_up(): void
    {
        parent::set_up();
        // Reset registry
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        if ($ref->hasProperty('builder_widgets')) {
            $b = $ref->getProperty('builder_widgets');
            $b->setAccessible(true);
            $b->setValue([]);
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
        $req = new WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets');
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
        $req = new WP_REST_Request('GET', '/artpulse/v1/dashboard-widgets');
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
        $req = new WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
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

        $saved = get_option('artpulse_dashboard_widgets_administrator');
        $this->assertIsArray($saved);
        $this->assertEquals([
            'role' => 'administrator',
            'layout' => [
                ['id' => 'foo', 'visible' => true],
                ['id' => 'bar', 'visible' => false],
            ],
        ], $saved);
    }
}
