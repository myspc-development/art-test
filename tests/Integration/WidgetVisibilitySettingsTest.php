<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use OrgAnalyticsWidget;

class WidgetVisibilitySettingsTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        // reset registry
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        delete_option('ap_widget_visibility_settings');
    }

    public function test_settings_override_roles_and_capability(): void {
        DashboardWidgetRegistry::register_widget('test_widget', [
            'label' => 'Test Widget',
            'callback' => '__return_null',
            'roles' => ['member'],
        ]);

        update_option('ap_widget_visibility_settings', [
            'test_widget' => [
                'roles' => ['organization'],
                'capability' => 'edit_posts',
            ],
        ]);

        $all = DashboardWidgetRegistry::get_all();
        $this->assertSame(['organization'], $all['test_widget']['roles']);
        $this->assertSame('edit_posts', $all['test_widget']['capability']);
    }

    public function test_org_analytics_widget_fallback_for_non_capable_user(): void {
        $uid = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($uid);

        ob_start();
        OrgAnalyticsWidget::render();
        $html = ob_get_clean();
        $this->assertStringContainsString('ap-widget-no-access', $html);
    }

    public function test_org_analytics_widget_renders_for_capable_user(): void {
        $uid = self::factory()->user->create(['role' => 'organization']);
        wp_set_current_user($uid);

        ob_start();
        OrgAnalyticsWidget::render();
        $html = ob_get_clean();
        $this->assertStringNotContainsString('ap-widget-no-access', $html);
        $this->assertStringContainsString('Basic traffic', $html);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_org_analytics_widget_hidden_in_builder_preview(): void {
        define('IS_DASHBOARD_BUILDER_PREVIEW', true);
        $uid = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($uid);

        ob_start();
        OrgAnalyticsWidget::render();
        $html = ob_get_clean();
        $this->assertSame('', $html);
    }

    public function test_visibility_page_reflects_saved_settings(): void {
        DashboardWidgetRegistry::register_widget('test_widget', [
            'label' => 'Test Widget',
            'callback' => '__return_null',
            'roles' => ['member'],
        ]);

        update_option('ap_widget_visibility_settings', [
            'test_widget' => [
                'roles' => ['member'],
                'capability' => 'edit_posts',
            ],
        ]);

        ob_start();
        ap_render_widget_visibility_page();
        $html = ob_get_clean();

        $this->assertStringContainsString('value="member" checked', $html);
        $this->assertStringContainsString('value="edit_posts"', $html);
    }
}
