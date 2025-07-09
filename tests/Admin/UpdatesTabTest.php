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
    function get_temp_dir() { return sys_get_temp_dir(); }
    function wp_generate_password($length = 12, $special_chars = false) { return 'pass'; }
    function wp_mkdir_p($dir) { mkdir($dir, 0777, true); }
    function update_option($key, $value) { \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] = $value; }
    function get_option($key, $default = '') { return \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] ?? $default; }
    function current_time($type = 'mysql') { return 'now'; }
    function delete_option($key) { unset(\ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key]); }
    function esc_html($text) { return $text; }
    function esc_html_e($text, $domain = '') { echo $text; }
    function wp_nonce_field($action) {}
    function esc_url($url) { return $url; }
    function wp_die($msg = '') { \ArtPulse\Admin\Tests\UpdatesTabTest::$died = $msg ?: true; }
    function check_admin_referer($action) {
        \ArtPulse\Admin\Tests\UpdatesTabTest::$checked_action = $action;
        if (($_REQUEST['_wpnonce'] ?? '') !== 'valid') {
            wp_die('invalid');
            throw new \Exception('die');
        }
    }
    function wp_remote_get($url, $args = []) { return ['body' => json_encode(['sha' => 'def'])]; }
    function wp_remote_retrieve_body($res) { return $res['body']; }
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
    public static $died = null;
    public static string $checked_action = '';
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
        self::$died = null;
        self::$checked_action = '';
        $_REQUEST = [];
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
        $_REQUEST['_wpnonce'] = 'valid';
        try {
            UpdatesTab::run_update();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertSame('ap_run_update', self::$checked_action);
        $this->assertSame('/admin.php?page=artpulse-settings?ap_update_success=1#updates', self::$redirect);
        $this->assertSame(['file1.txt', 'dir/file2.php'], self::$options['ap_updated_files'] ?? []);
        $this->assertSame(self::$zip, self::$unzipped[0]);
        $this->assertStringStartsWith(sys_get_temp_dir(), self::$unzipped[1]);
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

    public function test_run_update_invalid_nonce_dies(): void
    {
        $_REQUEST['_wpnonce'] = 'bad';
        try {
            UpdatesTab::run_update();
        } catch (\Exception $e) {
            $this->assertSame('die', $e->getMessage());
        }
        $this->assertSame('ap_run_update', self::$checked_action);
        $this->assertNotNull(self::$died);
    }

    public function test_check_updates_valid_nonce(): void
    {
        $_REQUEST['_wpnonce'] = 'valid';
        self::$options['artpulse_settings'] = ['update_repo_url' => 'https://github.com/foo/bar'];
        try {
            UpdatesTab::check_updates();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertSame('ap_check_updates', self::$checked_action);
        $this->assertSame(1, self::$options['ap_update_available'] ?? null);
    }

    public function test_check_updates_invalid_nonce_dies(): void
    {
        $_REQUEST['_wpnonce'] = 'bad';
        self::$options['artpulse_settings'] = ['update_repo_url' => 'https://github.com/foo/bar'];
        try {
            UpdatesTab::check_updates();
        } catch (\Exception $e) {
            $this->assertSame('die', $e->getMessage());
        }
        $this->assertSame('ap_check_updates', self::$checked_action);
        $this->assertNotNull(self::$died);
    }
}
