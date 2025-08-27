<?php
require_once __DIR__ . '/../TestStubs.php';

if (!defined('ARTPULSE_PLUGIN_DIR')) {
    define('ARTPULSE_PLUGIN_DIR', dirname(__DIR__, 2));
}
if (!function_exists('get_query_var')) {
    function get_query_var($var) { return $var === 'ap_dashboard' ? '1' : ''; }
}
if (!function_exists('is_page')) {
    function is_page($slug) { return false; }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() { return true; }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) { return true; }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return dirname(__DIR__, 2) . '/'; }
}

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Tests\Stubs\MockStorage;

final class DashboardTemplateIncludeTest extends TestCase {
    public function test_template_include_returns_dashboard_template(): void {
        $_GET['ap_dashboard'] = '1';
        MockStorage::$current_roles = ['view_artpulse_dashboard'];
        $tpl = DashboardController::template_include('index.php');
        $this->assertStringContainsString('templates/simple-dashboard.php', $tpl);
    }
}
