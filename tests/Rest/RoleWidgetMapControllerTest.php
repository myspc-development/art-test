<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\RoleWidgetMapController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class RoleWidgetMapControllerTest extends \WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();

        // Reset registry
        $ref  = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        // Ensure roles exist
        foreach ([ 'member', 'artist', 'organization' ] as $role) {
            if (!get_role($role)) {
                add_role($role, ucfirst($role));
            }
        }

        DashboardWidgetRegistry::register('alpha', [
            'title'           => 'Alpha',
            'render_callback' => '__return_null',
            'roles'           => ['member'],
        ]);
        DashboardWidgetRegistry::register('beta', [
            'title'           => 'Beta',
            'render_callback' => '__return_null',
            'roles'           => ['artist'],
        ]);
        DashboardWidgetRegistry::register('gamma', [
            'title'           => 'Gamma',
            'render_callback' => '__return_null',
        ]);

        RoleWidgetMapController::register();
        do_action('rest_api_init');

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
    }

    public function test_get_role_widget_map(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/role-widget-map');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());

        $data  = $res->get_data();
        $roles = ['member', 'artist', 'organization'];
        foreach ($roles as $role) {
            $this->assertArrayHasKey($role, $data, 'Missing role ' . $role);
            foreach ($data[$role] as $id) {
                $this->assertIsString($id);
                $this->assertNotSame('', $id);
            }
        }
    }
}
