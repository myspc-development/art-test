<?php
namespace ArtPulse\Dashboard;
function error_log($msg) { \WidgetGuardTest::$logs[] = $msg; }

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

class WidgetGuardTest extends WP_UnitTestCase
{
    public static array $logs = [];
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

    public function set_up(): void
    {
        parent::set_up();
        $this->reset_registry();
        self::$logs = [];
    }

    public function test_invalid_callback_gets_placeholder(): void
    {
        DashboardWidgetRegistry::register('bad', 'Bad', 'alert', 'desc', 'missing_cb');
        WidgetGuard::validate_and_patch('member');
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
        WidgetGuard::validate_and_patch('member');
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
        WidgetGuard::validate_and_patch('member');
        $cb = DashboardWidgetRegistry::get_widget_callback('bad2');
        ob_start();
        call_user_func($cb);
        $html = ob_get_clean();
        $this->assertStringContainsString('Widget callback is missing', $html);
        remove_all_filters('ap_widget_placeholder_enabled');
    }

    public function test_debug_summary_logged_when_patched(): void
    {
        DashboardWidgetRegistry::register('one', 'One', 'info', 'desc', 'missing_cb');
        DashboardWidgetRegistry::register('two', 'Two', 'info', 'desc', 'missing_cb');
        WidgetGuard::validate_and_patch();
        $this->assertNotEmpty(self::$logs);
        $summary = end(self::$logs);
        $this->assertStringContainsString('Patched widgets: one, two', $summary);
    }

    public function test_debug_filter_modifies_payload(): void
    {
        add_filter('ap_widget_placeholder_debug_payload', function ($args, $id) {
            $args['debug'] = $id;
            return $args;
        }, 10, 2);
        DashboardWidgetRegistry::register('foo', 'Foo', 'info', 'desc', 'missing_cb');
        WidgetGuard::validate_and_patch();
        $cb = DashboardWidgetRegistry::get_widget_callback('foo');
        ob_start();
        call_user_func($cb);
        $html = ob_get_clean();
        $this->assertStringContainsString('foo', $html);
        remove_all_filters('ap_widget_placeholder_debug_payload');
    }
}
