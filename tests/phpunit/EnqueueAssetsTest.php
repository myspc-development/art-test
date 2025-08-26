<?php
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use ArtPulse\Admin\EnqueueAssets;

/**
* Requires:
*   - composer require --dev phpunit/phpunit:^9.6 brain/monkey:^2.6
*   - tests/bootstrap.php that loads Composer autoload and defines ARTPULSE_PLUGIN_FILE
*
* If you don't have a bootstrap, minimally:
*   - define('ARTPULSE_PLUGIN_FILE', __FILE__);
*   - require_once __DIR__ . '/../../src/Admin/EnqueueAssets.php';
*/

// Ensure Composer + Brain Monkey are available even if dev autoload was dumped without dev
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
if (!class_exists(\Brain\Monkey\Functions::class)) {
    $bm = __DIR__ . '/../../vendor/brain/monkey/src';
    if (is_dir($bm)) {
        foreach (['Functions','Actions','Filters','Expectations','Patchers','Monkey'] as $f) {
            $p = $bm . '/' . $f . '.php';
            if (file_exists($p)) require_once $p;
        }
    }
}

class EnqueueAssetsTest extends TestCase
{
    private array $enqueuedScripts;
    private array $enqueuedStyles;
    private array $registeredScripts;
    private array $fs;
    protected function setUp(): void
    {
        Monkey\setUp();
        // ---- Tracking ----
        $this->enqueuedScripts   = [];
        $this->enqueuedStyles    = [];
        $this->registeredScripts = [];
        $this->fs                = []; // path => exists?
        // ---- Paths/URLs ----
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        Functions::when('plugin_dir_path')->alias(function ($file) {
            return '/p/'; // fake plugin dir
        });
        Functions::when('plugin_dir_url')->alias(function ($file) {
            return 'https://example.test/p/';
        });
        // ---- File system shims ----
        Functions::when('file_exists')->alias(function (string $path) {
            return $this->fs[$path] ?? false;
        });
        Functions::when('filemtime')->alias(function (string $path) {
            return 1234567890;
        });
        // ---- WP enqueue/register shims ----
        Functions::when('wp_enqueue_style')->alias(function ($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
            $this->enqueuedStyles[$handle] = compact('handle','src','deps','ver','media');
        });
        Functions::when('wp_style_is')->alias(function ($handle, $list = 'enqueued') {
            return $list === 'enqueued' ? isset($this->enqueuedStyles[$handle]) : false;
        });
        Functions::when('wp_register_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            $this->registeredScripts[$handle] = compact('handle','src','deps','ver','in_footer');
        });
        Functions::when('wp_enqueue_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            // If caller passes only handle (already registered), keep deps/src if known
            if ($src === '' && isset($this->registeredScripts[$handle])) {
                $reg = $this->registeredScripts[$handle];
                $src = $reg['src']; $deps = $reg['deps']; $ver = $reg['ver']; $in_footer = $reg['in_footer'];
            }
            $this->enqueuedScripts[$handle] = compact('handle','src','deps','ver','in_footer');
        });
        Functions::when('wp_script_is')->alias(function ($handle, $list = 'enqueued') {
            if ($list === 'registered') return isset($this->registeredScripts[$handle]);
            if ($list === 'enqueued')  return isset($this->enqueuedScripts[$handle]);
            return false;
        });
        // ---- Hook system ----
        // Brain Monkey already provides add_action/do_action implementations.
        // ---- Misc WP helpers used in code paths ----
        Functions::when('admin_url')->justReturn('https://example.test/wp-admin/admin-ajax.php');
        Functions::when('rest_url')->justReturn('https://example.test/wp-json/');
        Functions::when('wp_create_nonce')->justReturn('nonce');
        Functions::when('esc_url_raw')->alias(fn($u) => $u);
        Functions::when('esc_html__')->alias(fn($t,$d=null)=>$t);
        Functions::when('__')->alias(fn($t,$d=null)=>$t);
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
    }
    /** Utility: set a file as existing in our fake FS */
    private function fsTouch(string $rel): string
    {
        $path = rtrim('/p/', '/').'/'.ltrim($rel,'/');
        $this->fs[$path] = true;
        return $path;
    }
    /** Utility: fetch an enqueued script record by handle */
    private function script(string $handle): ?array
    {
        return $this->enqueuedScripts[$handle] ?? null;
    }
    /** Utility: fetch an enqueued style record by handle */
    private function style(string $handle): ?array
    {
        return $this->enqueuedStyles[$handle] ?? null;
    }
    public function test_register_adds_core_hooks(): void
    {
        Actions\expectAdded('enqueue_block_editor_assets')->twice(); // scripts + styles
        Actions\expectAdded('admin_enqueue_scripts')->once();        // method
        Actions\expectAdded('wp_enqueue_scripts')->once();           // method
        // (There is also an anonymous admin_enqueue_scripts closure we don't assert explicitly.)
        EnqueueAssets::register();
    }
    public function test_dashboard_admin_enqueues_with_sortable(): void
    {
        EnqueueAssets::register();
        // Provide all expected files
        $this->fsTouch('assets/css/dashboard.css');
        $this->fsTouch('assets/js/dashboard-role-tabs.js');
        $this->fsTouch('assets/js/role-dashboard.js');
        $this->fsTouch('assets/libs/sortablejs/Sortable.min.js');
        // Fire the admin hook for the dashboard page
        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');
        // Assertions
        $this->assertNotNull($this->style('ap-dashboard'), 'dashboard.css should be enqueued');
        $tabs = $this->script('ap-role-tabs');
        $this->assertNotNull($tabs, 'dashboard-role-tabs.js should be enqueued');
        $sortable = $this->script('sortablejs');
        $this->assertNotNull($sortable, 'SortableJS should be enqueued when present');
        $roleDash = $this->script('role-dashboard');
        $this->assertNotNull($roleDash, 'role-dashboard.js should be enqueued');
        // Ensure deps include tabs and sortable
        $this->assertContains('ap-role-tabs', $roleDash['deps']);
        $this->assertContains('sortablejs',   $roleDash['deps']);
    }
    public function test_dashboard_admin_enqueues_without_sortable(): void
    {
        EnqueueAssets::register();
        // Present core assets, omit SortableJS
        $this->fsTouch('assets/css/dashboard.css');
        $this->fsTouch('assets/js/dashboard-role-tabs.js');
        $this->fsTouch('assets/js/role-dashboard.js');
        // no sortable touch
        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');
        $roleDash = $this->script('role-dashboard');
        $this->assertNotNull($roleDash, 'role-dashboard.js should be enqueued');
        $this->assertContains('ap-role-tabs', $roleDash['deps']);
        $this->assertNotContains('sortablejs', $roleDash['deps'], 'Should not depend on sortable if file missing');
    }
    public function test_chart_js_is_registered_in_admin(): void
    {
        // Simulate an admin screen that passes the ArtPulse check
        $screen = new class {
            public string $id = 'artpulse-settings';
        };
        Functions::when('get_current_screen')->justReturn($screen);
        // Chart.js file exists
        $this->fsTouch('assets/libs/chart.js/4.4.1/chart.min.js');
        // Also ensure the dependent script exists so enqueue path proceeds
        $this->fsTouch('assets/js/ap-user-dashboard.js');
        $this->fsTouch('assets/css/ap-style.css');
        EnqueueAssets::enqueue_admin();
        $this->assertArrayHasKey('chart-js', $this->registeredScripts, 'Chart.js should be registered in admin');
        $userDash = $this->script('ap-user-dashboard-js');
        $this->assertNotNull($userDash, 'ap-user-dashboard-js should be enqueued');
        $this->assertContains('chart-js', $userDash['deps'], 'ap-user-dashboard-js must depend on chart-js');
    }
    public function test_block_editor_styles_enqueue(): void
    {
        // Editor screen
        $screen = new class {
            public function is_block_editor() { return true; }
        };
        Functions::when('get_current_screen')->justReturn($screen);
        $this->fsTouch('assets/css/editor-styles.css');
        EnqueueAssets::enqueue_block_editor_styles();
        $this->assertNotNull($this->style('artpulse-editor-styles'), 'Editor styles should be enqueued on block editor screens');
    }
    public function test_analytics_handle_is_consistent(): void
    {
        // Admin screen that matches ArtPulse
        $screen = new class {
            public string $id = 'artpulse-overview';
        };
        Functions::when('get_current_screen')->justReturn($screen);
        $this->fsTouch('assets/js/ap-analytics.js');
        EnqueueAssets::enqueue_admin();
        $this->assertNotNull($this->script('ap-analytics'), 'Analytics should enqueue with handle "ap-analytics"');
        $this->assertNull($this->script('ap-analytics-js'), 'Handle "ap-analytics-js" should not be used');
    }
}
