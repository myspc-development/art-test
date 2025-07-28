<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\WidgetEditorController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class WidgetEditorControllerTest extends \WP_UnitTestCase
{
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $this->user_id = self::factory()->user->create(['role' => 'artist']);
        wp_set_current_user($this->user_id);

        DashboardWidgetRegistry::register_widget('alpha', [
            'label' => 'Alpha',
            'callback' => '__return_null',
            'roles' => ['member'],
        ]);
        DashboardWidgetRegistry::register_widget('beta', [
            'label' => 'Beta',
            'callback' => '__return_null',
            'roles' => ['artist'],
        ]);

        WidgetEditorController::register();
        do_action('rest_api_init');
    }

    public function test_get_widgets_filters_by_current_user_role(): void
    {
        $req = new WP_REST_Request('GET', '/artpulse/v1/widgets');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $ids = array_column($res->get_data(), 'id');
        sort($ids);
        $this->assertSame(['beta'], $ids);
    }
}
