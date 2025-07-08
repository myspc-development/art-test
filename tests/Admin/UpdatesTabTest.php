<?php
namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', sys_get_temp_dir() . '/aptests/');
    }
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', __FILE__);
    }
    function current_user_can($cap) { return \ArtPulse\Admin\Tests\UpdatesTabTest::$can; }
    function admin_url($path = '') { return $path; }
    function add_query_arg($params, $url) { return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params); }
    function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\UpdatesTabTest::$redirect = $url; throw new \Exception('redirect'); }
    function download_url($url, $timeout = 300, $filename = '', $args = []) { return \ArtPulse\Admin\Tests\UpdatesTabTest::create_zip(); }
    function unzip_file($file, $dest) { \ArtPulse\Admin\Tests\UpdatesTabTest::$unzipped = [$file, $dest]; return true; }
    function is_wp_error($thing) { return false; }
    function plugin_dir_path($file) { return '/dest'; }
    function update_option($key, $value) { \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] = $value; }
    function get_option($key, $default = '') { return \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] ?? $default; }
    function current_time($type = 'mysql') { return 'now'; }
    function delete_option($key) { unset(\ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key]); }
    function esc_html($text) { return $text; }
    function esc_html_e($text, $domain = '') { echo $text; }
    function wp_nonce_field($action) {}
    function esc_url($url) { return $url; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UpdatesTab;

class UpdatesTabTest extends TestCase
{
    public static bool $can = true;
    public static string $redirect = '';
    public static array $options = [];
    public static array $unzipped = [];
    private static string $zip = '';

    public static function create_zip(): string
    {
        self::$zip = tempnam(sys_get_temp_dir(), 'apzip');
        $zip = new \ZipArchive();
        $zip->open(self::$zip, \ZipArchive::CREATE);
        $zip->addFromString('file1.txt', 'one');
        $zip->addFromString('dir/file2.php', 'two');
        $zip->close();
        return self::$zip;
    }

    protected function setUp(): void
    {
        self::$can = true;
        self::$redirect = '';
        self::$options = ['ap_update_remote_sha' => 'abc'];
        self::$unzipped = [];
        if (!is_dir(ABSPATH . 'wp-admin/includes')) {
            mkdir(ABSPATH . 'wp-admin/includes', 0777, true);
            file_put_contents(ABSPATH . 'wp-admin/includes/file.php', '<?php');
            file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php');
        }
    }

    protected function tearDown(): void
    {
        if (self::$zip && file_exists(self::$zip)) {
            unlink(self::$zip);
        }
    }

    public function test_run_update_stores_file_list_and_redirects(): void
    {
        try {
            UpdatesTab::run_update();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertSame('/admin.php?page=artpulse-settings?ap_update_success=1#updates', self::$redirect);
        $this->assertSame(['file1.txt', 'dir/file2.php'], self::$options['ap_updated_files'] ?? []);
        $this->assertSame([self::$zip, '/dest'], self::$unzipped);
    }

    public function test_render_outputs_summary_and_clears_option(): void
    {
        self::$options['ap_updated_files'] = ['foo.php'];
        $_GET['ap_update_success'] = '1';
        ob_start();
        UpdatesTab::render();
        $html = ob_get_clean();
        $this->assertStringContainsString('<li>foo.php</li>', $html);
        $this->assertArrayNotHasKey('ap_updated_files', self::$options);
    }
}
