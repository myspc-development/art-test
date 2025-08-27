<?php
require_once __DIR__ . '/../TestStubs.php';

if (!defined('ARTPULSE_PLUGIN_DIR')) {
    define('ARTPULSE_PLUGIN_DIR', dirname(__DIR__, 2));
}

// Allow tests to toggle gating conditions and auth checks.
$mock_query_var         = '';
$mock_is_page_dashboard = false;
$mock_is_user_logged_in = true;

if (!function_exists('get_query_var')) {
    function get_query_var($var) {
        global $mock_query_var;
        return $var === 'ap_dashboard' ? $mock_query_var : '';
    }
}
if (!function_exists('is_page')) {
    function is_page($slug) {
        global $mock_is_page_dashboard;
        return $slug === 'dashboard' && $mock_is_page_dashboard;
    }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        global $mock_is_user_logged_in;
        return $mock_is_user_logged_in;
    }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return dirname(__DIR__, 2) . '/'; }
}

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Tests\Stubs\MockStorage;

final class DashboardTemplateIncludeTest extends TestCase {
    protected function setUp(): void {
        global $mock_query_var, $mock_is_page_dashboard, $mock_is_user_logged_in;
        $mock_query_var         = '';
        $mock_is_page_dashboard = false;
        $mock_is_user_logged_in = true;
        $_GET = [];
        MockStorage::$current_roles = [];
    }

    public function test_returns_dashboard_template_when_query_var_and_authorized(): void {
        global $mock_query_var;
        $mock_query_var = '1';
        $_GET['ap_dashboard'] = '1';
        MockStorage::$current_roles = ['view_artpulse_dashboard'];
        $tpl = DashboardController::interceptTemplate('index.php');
        $this->assertStringContainsString('templates/simple-dashboard.php', $tpl);
    }

    public function test_returns_dashboard_template_when_slug_and_authorized(): void {
        global $mock_is_page_dashboard;
        $mock_is_page_dashboard = true;
        MockStorage::$current_roles = ['view_artpulse_dashboard'];
        $tpl = DashboardController::interceptTemplate('index.php');
        $this->assertStringContainsString('templates/simple-dashboard.php', $tpl);
    }

    public function test_returns_original_template_when_unauthorized(): void {
        global $mock_query_var;
        $mock_query_var = '1';
        $_GET['ap_dashboard'] = '1';
        // User logged in but lacks capability
        $tpl = DashboardController::interceptTemplate('index.php');
        $this->assertSame('index.php', $tpl);
    }

    public function test_does_not_intercept_unrelated_routes(): void {
        MockStorage::$current_roles = ['view_artpulse_dashboard'];
        $tpl = DashboardController::interceptTemplate('index.php');
        $this->assertSame('index.php', $tpl);
    }
}
