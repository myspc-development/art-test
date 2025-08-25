<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;
use ArtPulse\Admin\EnqueueAssets;

class EnqueueAssetsTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    private function makeBase(array $files): string {
        $base = sys_get_temp_dir() . '/ap-test-' . uniqid();
        foreach ($files as $file) {
            $path = $base . '/' . $file;
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, '');
        }
        return $base;
    }

    public function test_register_chart_js_registers_when_file_exists(): void {
        $base = $this->makeBase(['assets/libs/chart.js/4.4.1/chart.min.js']);
        $mtime = filemtime($base . '/assets/libs/chart.js/4.4.1/chart.min.js');
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        when('wp_script_is')->alias(fn() => false);

        expect('wp_register_script')
            ->once()->with('chart-js', 'https://plugin/assets/libs/chart.js/4.4.1/chart.min.js', [], $mtime, true);

        $ref = new \ReflectionClass(EnqueueAssets::class);
        $m = $ref->getMethod('register_chart_js');
        $m->setAccessible(true);
        $m->invoke(null);
        $this->addToAssertionCount(1);
    }

    public function test_editor_styles_hook_uses_enqueue_block_editor_assets(): void {
        $base = $this->makeBase(['assets/css/editor.css']);
        $mtime = filemtime($base . '/assets/css/editor.css');
        $screen = (object) ['is_block_editor' => fn() => true];
        when('get_current_screen')->justReturn($screen);
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        when('wp_style_is')->alias(fn() => false);

        expect('wp_enqueue_style')
            ->once()->with('artpulse-editor-styles', 'https://plugin/assets/css/editor.css', [], $mtime);

        EnqueueAssets::enqueue_block_editor_styles();
        $this->addToAssertionCount(1);
    }

    public function test_dashboard_admin_enqueues_in_order(): void {
        $base = $this->makeBase([
            'assets/css/dashboard.css',
            'assets/js/dashboard-role-tabs.js',
            'assets/libs/sortablejs/Sortable.min.js',
            'assets/js/role-dashboard.js',
        ]);
        $m1 = filemtime($base . '/assets/css/dashboard.css');
        $m2 = filemtime($base . '/assets/js/dashboard-role-tabs.js');
        $m3 = filemtime($base . '/assets/libs/sortablejs/Sortable.min.js');
        $m4 = filemtime($base . '/assets/js/role-dashboard.js');
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        $enqueued = [];
        when('wp_script_is')->alias(function($handle,$state=null) use (&$enqueued){
            return $state === 'enqueued' && in_array($handle, $enqueued, true);
        });
        when('wp_style_is')->alias(fn() => false);

        expect('wp_enqueue_style')
            ->once()->ordered()->with('ap-dashboard', 'https://plugin/assets/css/dashboard.css', [], $m1);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-role-tabs', 'https://plugin/assets/js/dashboard-role-tabs.js', [], $m2, true)
            ->andReturnUsing(function($h) use (&$enqueued){ $enqueued[] = $h; });
        expect('wp_enqueue_script')
            ->once()->ordered()->with('sortablejs', 'https://plugin/assets/libs/sortablejs/Sortable.min.js', [], $m3, true)
            ->andReturnUsing(function($h) use (&$enqueued){ $enqueued[] = $h; });
        expect('wp_enqueue_script')
            ->once()->ordered()->with('role-dashboard', 'https://plugin/assets/js/role-dashboard.js', ['ap-role-tabs','sortablejs'], $m4, true)
            ->andReturnUsing(function($h) use (&$enqueued){ $enqueued[] = $h; });

        EnqueueAssets::enqueue_admin('toplevel_page_ap-dashboard');
        $this->addToAssertionCount(1);
    }

    public function test_user_dashboard_depends_on_chart_js(): void {
        $base = $this->makeBase([
            'assets/libs/chart.js/4.4.1/chart.min.js',
            'assets/js/ap-analytics.js',
            'assets/js/ap-user-dashboard.js',
        ]);
        $m1 = filemtime($base . '/assets/libs/chart.js/4.4.1/chart.min.js');
        $m2 = filemtime($base . '/assets/js/ap-analytics.js');
        $m3 = filemtime($base . '/assets/js/ap-user-dashboard.js');
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        when('wp_script_is')->alias(fn() => false);
        when('wp_style_is')->alias(fn() => false);

        expect('wp_register_script')
            ->once()->with('chart-js', 'https://plugin/assets/libs/chart.js/4.4.1/chart.min.js', [], $m1, true);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-analytics', 'https://plugin/assets/js/ap-analytics.js', [], $m2, true);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-user-dashboard-js', 'https://plugin/assets/js/ap-user-dashboard.js', ['wp-api-fetch','chart-js'], $m3, true);

        EnqueueAssets::enqueue_admin('any');
        $this->addToAssertionCount(1);
    }

    public function test_analytics_handle_consistent(): void {
        $base = $this->makeBase([
            'assets/libs/chart.js/4.4.1/chart.min.js',
            'assets/js/ap-analytics.js',
            'assets/js/ap-user-dashboard.js',
        ]);
        $m1 = filemtime($base . '/assets/libs/chart.js/4.4.1/chart.min.js');
        $m2 = filemtime($base . '/assets/js/ap-analytics.js');
        $m3 = filemtime($base . '/assets/js/ap-user-dashboard.js');
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        when('wp_script_is')->alias(fn() => false);
        when('wp_style_is')->alias(fn() => false);

        expect('wp_register_script')
            ->once()->with('chart-js', 'https://plugin/assets/libs/chart.js/4.4.1/chart.min.js', [], $m1, true);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-analytics', 'https://plugin/assets/js/ap-analytics.js', [], $m2, true);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-user-dashboard-js', 'https://plugin/assets/js/ap-user-dashboard.js', ['wp-api-fetch','chart-js'], $m3, true);

        EnqueueAssets::enqueue_admin('another');
        $this->addToAssertionCount(1);
    }

    public function test_import_export_tab_sanitized(): void {
        $_GET['tab'] = '<script>import_export</script>';
        $base = $this->makeBase([
            'assets/libs/papaparse/papaparse.min.js',
            'assets/js/ap-csv-import.js',
        ]);
        $m1 = filemtime($base . '/assets/libs/papaparse/papaparse.min.js');
        $m2 = filemtime($base . '/assets/js/ap-csv-import.js');
        when('plugin_dir_path')->justReturn($base . '/');
        when('plugin_dir_url')->justReturn('https://plugin/');
        when('wp_script_is')->alias(fn() => false);
        when('wp_style_is')->alias(fn() => false);

        expect('wp_enqueue_script')
            ->once()->ordered()->with('papaparse', 'https://plugin/assets/libs/papaparse/papaparse.min.js', [], $m1, true);
        expect('wp_enqueue_script')
            ->once()->ordered()->with('ap-csv-import', 'https://plugin/assets/js/ap-csv-import.js', ['papaparse','wp-api-fetch'], $m2, true);

        EnqueueAssets::enqueue_admin('toplevel_page_ap-settings');
        unset($_GET['tab']);
        $this->addToAssertionCount(1);
    }
}

