<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

use ArtPulse\Admin\EnqueueAssets;

final class EnqueueAssetsTest extends TestCase
{
    private array $enqueuedScripts;
    private array $enqueuedStyles;
    private array $registeredScripts;
    private array $fs;

    protected function setUp(): void
    {
        Monkey\setUp(); // Brain Monkey on

        $this->enqueuedScripts   = [];
        $this->enqueuedStyles    = [];
        $this->registeredScripts = [];
        $this->fs                = [];

        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }

        // ---- Paths/URLs ----
        Functions\when('plugin_dir_path')->alias(fn($file) => '/p/');
        Functions\when('plugin_dir_url')->alias(fn($file) => 'https://example.test/p/');

        // ---- FS shims ----
        Functions\when('file_exists')->alias(fn(string $path) => $this->fs[$path] ?? false);
        Functions\when('filemtime')->alias(fn(string $path) => 1234567890);

        // ---- Enqueue/register shims ----
        Functions\when('wp_enqueue_style')->alias(function ($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
            $this->enqueuedStyles[$handle] = compact('handle','src','deps','ver','media');
        });
        Functions\when('wp_style_is')->alias(fn($handle, $list = 'enqueued') =>
            $list === 'enqueued' ? isset($this->enqueuedStyles[$handle]) : false
        );

        Functions\when('wp_register_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            $this->registeredScripts[$handle] = compact('handle','src','deps','ver','in_footer');
        });
        Functions\when('wp_enqueue_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            if ($src === '' && isset($this->registeredScripts[$handle])) {
                $reg = $this->registeredScripts[$handle];
                $src = $reg['src']; $deps = $reg['deps']; $ver = $reg['ver']; $in_footer = $reg['in_footer'];
            }
            $this->enqueuedScripts[$handle] = compact('handle','src','deps','ver','in_footer');
        });
        Functions\when('wp_script_is')->alias(function ($handle, $list = 'enqueued') {
            if ($list === 'registered') return isset($this->registeredScripts[$handle]);
            if ($list === 'enqueued')  return isset($this->enqueuedScripts[$handle]);
            return false;
        });

        // ---- Misc helpers touched in code ----
        Functions\when('admin_url')->justReturn('https://example.test/wp-admin/admin-ajax.php');
        Functions\when('rest_url')->justReturn('https://example.test/wp-json/');
        Functions\when('wp_create_nonce')->justReturn('nonce');
        Functions\when('esc_url_raw')->alias(fn($u) => $u);
        Functions\when('__')->alias(fn($t, $d=null) => $t);
        Functions\when('esc_html__')->alias(fn($t, $d=null) => $t);

        // get_current_screen is stubbed per-test where needed.
    }

    protected function tearDown(): void
    {
        Monkey\tearDown(); // Brain Monkey off
    }

    /** Utility: mark a file as present in fake FS */
    private function fsTouch(string $rel): string
    {
        $path = rtrim('/p/', '/') . '/' . ltrim($rel, '/');
        $this->fs[$path] = true;
        return $path;
    }

    private function script(string $handle): ?array { return $this->enqueuedScripts[$handle] ?? null; }
    private function style(string $handle): ?array  { return $this->enqueuedStyles[$handle] ?? null; }

    public function test_register_adds_core_hooks(): void
    {
        Actions\expectAdded('enqueue_block_editor_assets')->twice();
        Actions\expectAdded('admin_enqueue_scripts')->once();
        Actions\expectAdded('wp_enqueue_scripts')->once();

        EnqueueAssets::register();
        $this->assertTrue(true); // no exception means hooks added as expected
    }

    public function test_dashboard_admin_enqueues_with_sortable(): void
    {
        EnqueueAssets::register();

        $this->fsTouch('assets/css/dashboard.css');
        $this->fsTouch('assets/js/dashboard-role-tabs.js');
        $this->fsTouch('assets/js/role-dashboard.js');
        $this->fsTouch('assets/libs/sortablejs/Sortable.min.js');

        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');

        $this->assertNotNull($this->style('ap-dashboard'));
        $this->assertNotNull($this->script('ap-role-tabs'));
        $this->assertNotNull($this->script('sortablejs'));

        $roleDash = $this->script('role-dashboard');
        $this->assertNotNull($roleDash);
        $this->assertContains('ap-role-tabs', $roleDash['deps']);
        $this->assertContains('sortablejs',   $roleDash['deps']);
    }

    public function test_dashboard_admin_enqueues_without_sortable(): void
    {
        EnqueueAssets::register();

        $this->fsTouch('assets/css/dashboard.css');
        $this->fsTouch('assets/js/dashboard-role-tabs.js');
        $this->fsTouch('assets/js/role-dashboard.js');

        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');

        $roleDash = $this->script('role-dashboard');
        $this->assertNotNull($roleDash);
        $this->assertContains('ap-role-tabs', $roleDash['deps']);
        $this->assertNotContains('sortablejs', $roleDash['deps']);
    }

    public function test_chart_js_is_registered_in_admin(): void
    {
        $screen = new class { public string $id = 'artpulse-settings'; };
        Functions\when('get_current_screen')->justReturn($screen);

        $this->fsTouch('assets/libs/chart.js/4.4.1/chart.min.js');
        $this->fsTouch('assets/js/ap-user-dashboard.js');
        $this->fsTouch('assets/css/ap-style.css');

        EnqueueAssets::enqueue_admin();

        $this->assertArrayHasKey('chart-js', $this->registeredScripts);
        $userDash = $this->script('ap-user-dashboard-js');
        $this->assertNotNull($userDash);
        $this->assertContains('chart-js', $userDash['deps']);
    }

    public function test_block_editor_styles_enqueue(): void
    {
        $screen = new class { public function is_block_editor() { return true; } };
        Functions\when('get_current_screen')->justReturn($screen);

        $this->fsTouch('assets/css/editor-styles.css');

        EnqueueAssets::enqueue_block_editor_styles();

        $this->assertNotNull($this->style('artpulse-editor-styles'));
    }

    public function test_analytics_handle_is_consistent(): void
    {
        $screen = new class { public string $id = 'artpulse-overview'; };
        Functions\when('get_current_screen')->justReturn($screen);

        $this->fsTouch('assets/js/ap-analytics.js');

        EnqueueAssets::enqueue_admin();

        $this->assertNotNull($this->script('ap-analytics'));
        $this->assertNull($this->script('ap-analytics-js'));
    }
}
