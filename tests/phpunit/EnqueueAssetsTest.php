<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Brain\Monkey as Monkey;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use ArtPulse\Admin\EnqueueAssets;

final class EnqueueAssetsTest extends TestCase
{
    private array $enqueuedScripts = [];
    private array $enqueuedStyles  = [];
    private array $registeredScripts = [];
    private array $fs = [];

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }

        // Paths
        Functions\when('plugin_dir_path')->alias(fn($f) => '/p/');
        Functions\when('plugin_dir_url')->alias(fn($f) => 'https://example.test/p/');

        // File system
        Functions\when('file_exists')->alias(fn(string $p) => $this->fs[$p] ?? false);
        Functions\when('filemtime')->alias(fn(string $p) => 1234567890);

        // Script/style helpers
        Functions\when('wp_register_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            $this->registeredScripts[$handle] = [
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'in_footer' => $in_footer,
            ];
        });
        Functions\when('wp_enqueue_script')->alias(function ($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
            if ($src === '' && isset($this->registeredScripts[$handle])) {
                $r = $this->registeredScripts[$handle];
                $src = $r['src']; $deps = $r['deps']; $ver = $r['ver']; $in_footer = $r['in_footer'];
            }
            $this->enqueuedScripts[$handle] = [
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'in_footer' => $in_footer,
            ];
        });
        Functions\when('wp_script_is')->alias(function ($handle, $list = 'enqueued') {
            if ($list === 'registered') {
                return isset($this->registeredScripts[$handle]);
            }
            if ($list === 'enqueued') {
                return isset($this->enqueuedScripts[$handle]);
            }
            return false;
        });
        Functions\when('wp_enqueue_style')->alias(function ($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
            $this->enqueuedStyles[$handle] = [
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'media' => $media,
            ];
        });
        Functions\when('wp_style_is')->alias(function ($handle, $list = 'enqueued') {
            return $list === 'enqueued' ? isset($this->enqueuedStyles[$handle]) : false;
        });

    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    private function touch(string $rel): void
    {
        $this->fs['/p/' . ltrim($rel, '/')] = true;
    }

    private function script(string $handle): ?array
    {
        return $this->enqueuedScripts[$handle] ?? null;
    }

    private function style(string $handle): ?array
    {
        return $this->enqueuedStyles[$handle] ?? null;
    }

    public function test_register_wires_hooks(): void
    {
        Actions\expectAdded('enqueue_block_editor_assets')->twice();
        Actions\expectAdded('admin_enqueue_scripts')->once();
        Actions\expectAdded('wp_enqueue_scripts')->once();
        EnqueueAssets::register();
        $this->assertTrue(true);
    }

    public function test_dashboard_admin_enqueues_with_sortable(): void
    {
        $cb = null;
        Actions\expectAdded('admin_enqueue_scripts')->once()->whenHappen(function ($callback) use (&$cb) {
            $cb = $callback;
        });
        EnqueueAssets::register();
        Actions\expectDone('admin_enqueue_scripts')->once()->whenHappen(function ($hook) use (&$cb) {
            call_user_func($cb, $hook);
        });

        $this->touch('assets/css/dashboard.css');
        $this->touch('assets/js/dashboard-role-tabs.js');
        $this->touch('assets/js/role-dashboard.js');
        $this->touch('assets/libs/sortablejs/Sortable.min.js');

        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');

        $this->assertNotNull($this->style('ap-dashboard'));
        $this->assertNotNull($this->script('ap-role-tabs'));
        $this->assertNotNull($this->script('sortablejs'));
        $role = $this->script('role-dashboard');
        $this->assertNotNull($role);
        $this->assertContains('ap-role-tabs', $role['deps']);
        $this->assertContains('sortablejs', $role['deps']);
    }

    public function test_dashboard_admin_enqueues_without_sortable(): void
    {
        $cb = null;
        Actions\expectAdded('admin_enqueue_scripts')->once()->whenHappen(function ($callback) use (&$cb) {
            $cb = $callback;
        });
        EnqueueAssets::register();
        Actions\expectDone('admin_enqueue_scripts')->once()->whenHappen(function ($hook) use (&$cb) {
            call_user_func($cb, $hook);
        });

        $this->touch('assets/css/dashboard.css');
        $this->touch('assets/js/dashboard-role-tabs.js');
        $this->touch('assets/js/role-dashboard.js');

        do_action('admin_enqueue_scripts', 'toplevel_page_ap-dashboard');

        $role = $this->script('role-dashboard');
        $this->assertNotNull($role);
        $this->assertContains('ap-role-tabs', $role['deps']);
        $this->assertNotContains('sortablejs', $role['deps']);
    }

    public function test_chart_js_registered_in_admin(): void
    {
        $this->touch('assets/libs/chart.js/4.4.1/chart.min.js');
        $this->touch('assets/js/ap-user-dashboard.js');

        EnqueueAssets::enqueue_admin('artpulse-settings');

        $this->assertArrayHasKey('chart-js', $this->registeredScripts);
        $dash = $this->script('ap-user-dashboard-js');
        $this->assertNotNull($dash);
        $this->assertContains('chart-js', $dash['deps']);
    }

    public function test_block_editor_styles_enqueue(): void
    {
        $screen = new class {
            public function is_block_editor(): bool { return true; }
        };
        Functions\when('get_current_screen')->justReturn($screen);
        $this->touch('assets/css/editor.css');

        EnqueueAssets::enqueue_block_editor_styles();

        $this->assertNotNull($this->style('artpulse-editor-styles'));
    }

    public function test_import_export_tab_enqueues(): void
    {
        $this->touch('assets/libs/papaparse/papaparse.min.js');
        $this->touch('assets/js/ap-csv-import.js');

        Functions\when('get_current_screen')->justReturn(new class {
            public string $id = 'artpulse-settings';
        });
        $_GET['tab'] = 'import_export';

        EnqueueAssets::enqueue_admin('artpulse-settings');

        $this->assertNotNull($this->script('papaparse'));
        $csv = $this->script('ap-csv-import');
        $this->assertNotNull($csv);
        $this->assertContains('papaparse', $csv['deps']);
        $this->assertContains('wp-api-fetch', $csv['deps']);

        unset($_GET['tab']);
    }
}

