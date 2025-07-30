<?php
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

class WidgetGuardTest extends WP_UnitTestCase
{
    private function reset_registry(): void
    {
        $ref  = new ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        $prop = $ref->getProperty('id_map');
        $prop->setAccessible(true);
        $prop->setValue(null);
    }

    protected function set_up(): void
    {
        parent::set_up();
        $this->reset_registry();
    }

    public function test_invalid_callback_gets_placeholder(): void
    {
        DashboardWidgetRegistry::register('bad', 'Bad', 'alert', 'desc', 'missing_cb');
        WidgetGuard::validate_and_patch();
        $cb = DashboardWidgetRegistry::get_widget_callback('bad');
        ob_start();
        call_user_func($cb, 1);
        $html = ob_get_clean();
        $this->assertStringContainsString('Widget Unavailable', $html);
    }

    public function test_valid_widget_unchanged(): void
    {
        DashboardWidgetRegistry::register('good', 'Good', 'info', 'desc', static function () {
            echo 'OK';
        });
        WidgetGuard::validate_and_patch();
        $cb = DashboardWidgetRegistry::get_widget_callback('good');
        ob_start();
        call_user_func($cb, 1);
        $html = ob_get_clean();
        $this->assertSame('OK', $html);
    }

    public function test_feature_flag_disabled_does_nothing(): void
    {
        add_filter('ap_widget_placeholder_enabled', '__return_false');
        DashboardWidgetRegistry::register('bad2', 'Bad2', 'info', 'desc', 'missing_cb');
        WidgetGuard::validate_and_patch();
        $cb = DashboardWidgetRegistry::get_widget_callback('bad2');
        ob_start();
        call_user_func($cb);
        $html = ob_get_clean();
        $this->assertStringContainsString('Widget callback is missing', $html);
        remove_all_filters('ap_widget_placeholder_enabled');
    }
}
