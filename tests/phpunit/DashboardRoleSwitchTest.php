<?php
declare(strict_types=1);

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use function Patchwork\redefine;
use function Patchwork\restore;

if (!defined('AP_VERBOSE_DEBUG')) {
    define('AP_VERBOSE_DEBUG', true);
}
if (!defined('ARTPULSE_PLUGIN_DIR')) {
    define('ARTPULSE_PLUGIN_DIR', dirname(__DIR__, 2));
}
if (!function_exists('get_query_var')) {
    function get_query_var($key) { return $_GET[$key] ?? ''; }
}
if (!function_exists('set_query_var')) {
    function set_query_var($key, $val) { $_GET[$key] = $val; }
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

final class DashboardRoleSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        header_remove();
        MockStorage::$current_roles = ['view_artpulse_dashboard', 'artist'];
        ini_set('error_log', '/tmp/phpunit-error.log');
        WidgetRegistry::register('widget_membership', static fn() => '<section></section>');
        WidgetRegistry::register('widget_artist_revenue_summary', static fn() => '<section></section>');
        WidgetRegistry::register('widget_audience_crm', static fn() => '<section></section>');
    }

    /**
     * @dataProvider roles
     * @runInSeparateProcess
     */
    public function test_role_param_sets_header_and_attributes(string $role): void
    {
        header_remove();
        $_GET['ap_dashboard'] = '1';
        $_GET['role']        = $role;
        $captured = [];
        $handle   = redefine('header', function ($string) use (&$captured) {
            $captured[] = $string;
        });

        $tpl = DashboardController::template_include('index.php');
        ob_start();
        include $tpl;
        $html = ob_get_clean();

        $this->assertStringContainsString(sprintf('id="ap-panel-%s"', $role), $html);
        $this->assertStringContainsString(sprintf('aria-labelledby="ap-tab-%s"', $role), $html);
        $this->assertStringContainsString(sprintf('data-role="%s"', $role), $html);

        $this->assertContains('X-AP-Resolved-Role: ' . $role, $captured);

        restore($handle);
        unset($_GET['role'], $_GET['ap_role'], $_GET['ap_dashboard']);
        header_remove();
    }

    public function roles(): array
    {
        return [['member'], ['artist'], ['organization']];
    }
}
