<?php
use ArtPulse\Core\DashboardWidgetRegistry;

class SampleHelloWidgetExistingTest extends WP_UnitTestCase {
    private function reset_registry(): void {
        $ref  = new ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        $prop = $ref->getProperty('id_map');
        $prop->setAccessible(true);
        $prop->setValue(null);
    }

    public function test_can_view_logged_in_and_logged_out(): void {
        $uid = self::factory()->user->create();
        wp_set_current_user($uid);
        $this->assertTrue(SampleHelloWidget::can_view());

        wp_set_current_user(0);
        $this->assertFalse(SampleHelloWidget::can_view());
    }

    public function test_shortcode_renders_widget_for_member(): void {
        $this->reset_registry();
        SampleHelloWidget::register();

        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);

        $html = do_shortcode('[ap_render_ui]');
        $this->assertStringContainsString('Hello', $html);
    }
}
