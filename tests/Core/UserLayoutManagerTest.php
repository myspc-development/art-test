<?php

use ArtPulse\Core\DashboardController;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

if (!function_exists('get_userdata')) {
    function get_userdata($user_id) { return (object) ['ID' => $user_id]; }
}
if (!function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key = '', $single = false) { return \UserLayoutManagerTest::$user_meta; }
}

class UserLayoutManagerTest extends \WP_UnitTestCase {
    public static array $user_meta = [];

    public function setUp(): void {
        parent::set_up();

        // Register some fake widgets for testing
        DashboardWidgetRegistry::register_widget([
            'id' => 'widget_news',
            'allowed_roles' => ['member', 'artist', 'organization'],
        ]);

        DashboardWidgetRegistry::register_widget([
            'id' => 'artist_only_widget',
            'allowed_roles' => ['artist'],
        ]);

        DashboardWidgetRegistry::register_widget([
            'id' => 'org_admin_stats',
            'capability' => 'manage_options',
        ]);
    }

    public function test_fallback_layout_for_member_role() {
        $user_id = 101;
        self::$user_meta = ['member'];

        $layout = UserLayoutManager::get_role_layout($user_id);

        $this->assertNotEmpty($layout, 'Fallback layout for member should not be empty.');
        foreach ($layout as $widget) {
            $this->assertArrayHasKey('id', $widget);
            $this->assertNotEmpty(DashboardWidgetRegistry::get_widget($widget['id'], $user_id), 'Widget must be registered and visible.');
        }
    }

    public function test_fallback_layout_for_artist_role() {
        $user_id = 102;
        self::$user_meta = ['artist'];

        $layout = UserLayoutManager::get_role_layout($user_id);
        $this->assertNotEmpty($layout, 'Fallback layout for artist should not be empty.');

        foreach ($layout as $widget) {
            $this->assertArrayHasKey('id', $widget);
        }
    }

    public function test_layout_filters_out_unregistered_widgets() {
        $user_id = 103;
        self::$user_meta = ['organization'];

        // Simulate a layout with an unregistered widget
        $default = [
            ['id' => 'missing_widget'],
            ['id' => 'widget_news'],
        ];

        // Override preset temporarily
        DashboardController::set_test_presets([
            'organization' => $default,
        ]);

        $layout = UserLayoutManager::get_role_layout($user_id);

        $ids = array_column($layout, 'id');
        $this->assertNotContains('missing_widget', $ids, 'Unregistered widgets should be filtered.');
        $this->assertContains('widget_news', $ids, 'Registered widgets should be present.');
    }
}
