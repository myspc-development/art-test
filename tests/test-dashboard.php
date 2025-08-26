<?php
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\EventsWidget;
use ArtPulse\Widgets\DonationsWidget;

class DashboardRenderingTest extends WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        $ref = new ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        $prop = $ref->getProperty('id_map');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
        foreach (['member', 'artist', 'organization'] as $role) {
            if (!get_role($role)) {
                add_role($role, ucfirst($role));
            }
        }
        EventsWidget::register();
        DonationsWidget::register();
    }

    public function test_fallback_callback_used(): void {
        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        DashboardWidgetRegistry::register('bad', 'Bad', 'alert', 'bad', 'not_callable');
        $cb   = DashboardWidgetRegistry::get_widget_callback('bad');
        $html = $cb ? call_user_func($cb) : '';
        $this->assertStringContainsString('Widget callback is missing', $html);
    }

    public function test_widget_access_by_role(): void {
        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        ob_start();
        DashboardWidgetRegistry::render_for_role($uid);
        $html = ob_get_clean();
        $this->assertStringContainsString('ap-section-insights', $html);
        $this->assertStringNotContainsString('Donations Widget', $html);
    }

    public function test_shortcode_structure(): void {
        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
        $out = do_shortcode('[ap_render_ui]');
        $this->assertStringContainsString('ap-dashboard', $out);
    }
}
