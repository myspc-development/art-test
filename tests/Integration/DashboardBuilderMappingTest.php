<?php
namespace ArtPulse\Integration\Tests;

use WP_REST_Request;
use ArtPulse\Rest\DashboardWidgetController;
use ArtPulse\DashboardBuilder\DashboardWidgetRegistry as BuilderRegistry;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\UserDashboardManager;

class DashboardBuilderMappingTest extends \WP_UnitTestCase {
    private int $admin;

    public function set_up(): void {
        parent::set_up();
        // reset registries
        $ref = new \ReflectionClass(BuilderRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $ref2 = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop2 = $ref2->getProperty('widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([]);

        $this->admin = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin);

        // register builder widgets
        BuilderRegistry::register('news_feed', [
            'title' => 'News Feed',
            'render_callback' => '__return_null',
            'roles' => ['member']
        ]);
        BuilderRegistry::register('my_favorites', [
            'title' => 'Favorites',
            'render_callback' => '__return_null',
            'roles' => ['member']
        ]);

        // register core widgets
        DashboardWidgetRegistry::register_widget('widget_news', [
            'label' => 'News',
            'callback' => '__return_null',
            'roles' => ['member']
        ]);
        DashboardWidgetRegistry::register_widget('widget_my_favorites', [
            'label' => 'Favorites',
            'callback' => '__return_null',
            'roles' => ['member']
        ]);

        DashboardWidgetController::register();
        UserDashboardManager::register();
        do_action('rest_api_init');
    }

    public function test_builder_layout_maps_to_core_ids(): void {
        $req = new WP_REST_Request('POST', '/artpulse/v1/dashboard-widgets/save');
        $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $req->set_body_params([
            'role' => 'member',
            'layout' => [
                ['id' => 'news_feed', 'visible' => true],
                ['id' => 'my_favorites', 'visible' => false],
            ],
        ]);
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());

        $config = get_option('ap_dashboard_widget_config');
        $this->assertSame([
            'layout' => [
                ['id' => 'widget_news', 'visible' => true],
                ['id' => 'widget_my_favorites', 'visible' => false],
            ],
        ], $config['member']);

        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);

        $resp = UserDashboardManager::getDashboardLayout();
        $data = $resp->get_data();
        $this->assertSame(['widget_news', 'widget_my_favorites'], $data['layout']);
        $this->assertSame([
            'widget_news' => true,
            'widget_my_favorites' => false,
        ], $data['visibility']);
    }

    public function test_map_to_core_id_helper(): void {
        $expected = [
            'revenue_summary'      => 'artist_revenue_summary',
            'audience_crm'         => 'audience_crm',
            'branding_settings_panel' => 'branding_settings_panel',
        ];

        foreach ($expected as $builder => $core) {
            $this->assertSame(
                $core,
                DashboardWidgetRegistry::map_to_core_id($builder)
            );
        }
    }
}
