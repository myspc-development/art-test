<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetVisibilityTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        delete_option('ap_widget_group_visibility');
    }

    public function test_can_view_respected_in_render(): void {
        DashboardWidgetRegistry::register_widget('yes', [
            'label' => 'Yes',
            'callback' => [TestWidgetYes::class, 'render'],
            'roles' => ['member']
        ]);
        DashboardWidgetRegistry::register_widget('no', [
            'label' => 'No',
            'callback' => [TestWidgetNo::class, 'render'],
            'roles' => ['member']
        ]);

        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        ob_start();
        DashboardWidgetRegistry::render_for_role($uid);
        $html = ob_get_clean();

        $this->assertStringContainsString('YES', $html);
        $this->assertStringNotContainsString('NO', $html);
    }

    public function test_group_visibility_option_hides_widgets(): void {
        DashboardWidgetRegistry::register_widget('grouped', [
            'label' => 'Grouped',
            'callback' => [TestWidgetYes::class, 'render'],
            'roles' => ['member'],
            'group' => 'beta'
        ]);
        update_option('ap_widget_group_visibility', ['beta' => false]);

        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        ob_start();
        DashboardWidgetRegistry::render_for_role($uid);
        $html = ob_get_clean();

        $this->assertStringNotContainsString('YES', $html);
    }
}

class TestWidgetYes {
    public static function can_view() { return true; }
    public static function render() { return 'YES'; }
}
class TestWidgetNo {
    public static function can_view() { return false; }
    public static function render() { return 'NO'; }
}
