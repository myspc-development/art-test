<?php
namespace {
    if (!class_exists('WP_CLI')) {
        class WP_CLI {
            public static function line($msg): void {}
            public static function success($msg): void {}
            public static function warning($msg): void {}
            public static function add_command($name, $callable): void {}
        }
    }
    if (!function_exists('update_option')) {
        function update_option($name, $value) { $GLOBALS['wp_opts'][$name] = $value; }
    }
    if (!function_exists('get_option')) {
        function get_option($name, $default = false) { return $GLOBALS['wp_opts'][$name] ?? $default; }
    }
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) { return strtolower(preg_replace('/[^a-z0-9_]/', '', $key)); }
    }
    if (!function_exists('do_action')) {
        function do_action($hook, ...$args) {}
    }
}

namespace ArtPulse\Cli\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Cli\WidgetAudit;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;
use ArtPulse\Widgets\TestWidget;

require_once __DIR__ . '/fixtures/TestWidget.php';

class WidgetAuditFixTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        foreach (['widgets','builder_widgets','id_map','issues','logged_duplicates','aliases'] as $prop) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, []);
            }
        }
        $GLOBALS['wp_opts'] = [];
    }

    public function test_fix_unhide_activate_and_bind(): void
    {
        update_option('artpulse_hidden_widgets', ['member' => ['widget_test']]);
        update_option('artpulse_widget_flags', ['widget_test' => ['status' => 'inactive']]);

        DashboardWidgetRegistry::register('widget_test', 'Test', '', '', [ApPlaceholderWidget::class, 'render']);

        $cmd = new WidgetAudit();
        $cmd->fix([], ['role' => 'member', 'unhide' => true, 'activate-all' => true]);

        $hidden = get_option('artpulse_hidden_widgets');
        $this->assertSame([], $hidden['member']);
        $flags = get_option('artpulse_widget_flags');
        $this->assertSame('active', $flags['widget_test']['status']);
        $def = DashboardWidgetRegistry::get('widget_test');
        $this->assertSame(TestWidget::class, $def['class']);
    }
}

}
