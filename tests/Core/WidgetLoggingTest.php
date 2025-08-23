<?php
namespace {
    // Simple hook system to mimic WordPress behavior.
    $GLOBALS['ap_wp_hooks'] = ['actions' => [], 'filters' => []];

    if (!function_exists('add_filter')) {
        function add_filter($hook, $callback, $priority = 10, $args = 1) {
            $GLOBALS['ap_wp_hooks']['filters'][$hook][] = $callback;
        }
    }
    if (!function_exists('apply_filters')) {
        function apply_filters($hook, $value) {
            foreach ($GLOBALS['ap_wp_hooks']['filters'][$hook] ?? [] as $cb) {
                $value = $cb($value);
            }
            return $value;
        }
    }
    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $args = 1) {
            $GLOBALS['ap_wp_hooks']['actions'][$hook][] = $callback;
        }
    }
    if (!function_exists('do_action')) {
        function do_action($hook, ...$args) {
            foreach ($GLOBALS['ap_wp_hooks']['actions'][$hook] ?? [] as $cb) {
                $cb(...$args);
            }
        }
    }
    if (!function_exists('__return_true')) {
        function __return_true() { return true; }
    }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;

class WidgetLoggingTest extends TestCase
{
    private string $logFile;
    private string $origErrorLog;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }

        add_filter('ap_enable_widget_logging', '__return_true');
        require_once __DIR__ . '/../../includes/widget-logging.php';

        $this->origErrorLog = ini_get('error_log');
        $this->logFile = tempnam(sys_get_temp_dir(), 'ap_widget_log');
        ini_set('error_log', $this->logFile);
    }

    protected function tearDown(): void
    {
        ini_set('error_log', $this->origErrorLog);
        @unlink($this->logFile);
        parent::tearDown();
    }

    public function test_widget_events_are_logged(): void
    {
        do_action('ap_widget_rendered', 'widget_id', 1);
        do_action('ap_widget_hidden', 'widget_id', 1);

        $log = file_get_contents($this->logFile);
        $this->assertStringContainsString('Widget widget_id rendered for user 1', $log);
        $this->assertStringContainsString('Widget widget_id hidden for user 1', $log);
    }
}

}
