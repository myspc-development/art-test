<?php
namespace ArtPulse\Admin;

if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap) {
        return \ArtPulse\Admin\Tests\UpdatesTabTest::$can;
    }
}
if (!function_exists(__NAMESPACE__ . '\\add_query_arg')) {
    function add_query_arg($params, $url) { return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params); }
}
if (!function_exists(__NAMESPACE__ . '\\wp_safe_redirect')) {
    function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\UpdatesTabTest::$redirect = $url; throw new \Exception('redirect'); }
}
if (!function_exists(__NAMESPACE__ . '\\download_url')) {
    function download_url($url, $timeout = 300, $filename = '', $args = []) {
        if (\ArtPulse\Admin\Tests\UpdatesTabTest::$download_error) {
            return \ArtPulse\Admin\Tests\UpdatesTabTest::$download_error;
        }
        return \ArtPulse\Admin\Tests\UpdatesTabTest::create_zip();
    }
}
    if (!function_exists(__NAMESPACE__ . '\\unzip_file')) {
        function unzip_file($file, $dest) { \ArtPulse\Admin\Tests\UpdatesTabTest::$unzipped = [$file, $dest]; return true; }
    }
    if (!function_exists(__NAMESPACE__ . '\\is_wp_error')) {
        function is_wp_error($thing) { return $thing instanceof WP_Error; }
    }
    if (!function_exists(__NAMESPACE__ . '\\plugin_dir_path')) {
        function plugin_dir_path($file) { return '/dest'; }
    }
    if (!function_exists(__NAMESPACE__ . '\\get_temp_dir')) {
        function get_temp_dir() { return sys_get_temp_dir(); }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_generate_password')) {
        function wp_generate_password($length = 12, $special_chars = false) { return 'pass'; }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_mkdir_p')) {
        function wp_mkdir_p($dir) { mkdir($dir, 0777, true); }
    }
    if (!function_exists(__NAMESPACE__ . '\\update_option')) {
        function update_option($key, $value) { \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] = $value; }
    }
    if (!function_exists(__NAMESPACE__ . '\\get_option')) {
        function get_option($key, $default = '') { return \ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key] ?? $default; }
    }
    if (!function_exists(__NAMESPACE__ . '\\current_time')) {
        function current_time($type = 'mysql') { return 'now'; }
    }
    if (!function_exists(__NAMESPACE__ . '\\delete_option')) {
        function delete_option($key) { unset(\ArtPulse\Admin\Tests\UpdatesTabTest::$options[$key]); }
    }
    if (!function_exists(__NAMESPACE__ . '\\esc_html')) {
        function esc_html($text) { return $text; }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_nonce_field')) {
        function wp_nonce_field($action) {}
    }
    if (!function_exists(__NAMESPACE__ . '\\esc_url')) {
        function esc_url($url) { return $url; }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_die')) {
        function wp_die($msg = '') { \ArtPulse\Admin\Tests\UpdatesTabTest::$died = $msg ?: true; }
    }
    if (!function_exists(__NAMESPACE__ . '\\check_admin_referer')) {
        function check_admin_referer($action) {
            \ArtPulse\Admin\Tests\UpdatesTabTest::$checked_action = $action;
            if (($_REQUEST['_wpnonce'] ?? '') !== 'valid') {
                wp_die('invalid');
                throw new \Exception('die');
            }
        }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_remote_get')) {
        function wp_remote_get($url, $args = []) {
            if (\ArtPulse\Admin\Tests\UpdatesTabTest::$remote_error) {
                return \ArtPulse\Admin\Tests\UpdatesTabTest::$remote_error;
            }
            return ['body' => json_encode(['sha' => 'def'])];
        }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_remote_retrieve_body')) {
        function wp_remote_retrieve_body($res) { return $res['body']; }
    }
    if (!function_exists(__NAMESPACE__ . '\\error_log')) {
        function error_log($msg) { \ArtPulse\Admin\Tests\UpdatesTabTest::$logs[] = $msg; }
    }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UpdatesTab;
use Brain\Monkey;
use Brain\Monkey\Functions;
use function ArtPulse\Tests\safe_unlink;

class UpdatesTabTest extends TestCase
{
    public static bool $can = true;
    public static string $redirect = '';
    public static array $options = [];
    public static array $unzipped = [];
    public static $died = null;
    public static string $checked_action = '';
    public static $download_error = null;
    public static $remote_error = null;
    public static array $logs = [];
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
        parent::setUp();
        Monkey\setUp();
        Functions\when('admin_url')->alias(fn($path = '') => $path);

        self::$can = true;
        self::$redirect = '';
        self::$options = ['ap_update_remote_sha' => 'abc'];
        self::$unzipped = [];
        self::$died = null;
        self::$checked_action = '';
        self::$download_error = null;
        self::$remote_error = null;
        self::$logs = [];
        $_REQUEST = [];
        if (!is_dir(ABSPATH . 'wp-admin/includes')) {
            mkdir(ABSPATH . 'wp-admin/includes', 0777, true);
            file_put_contents(ABSPATH . 'wp-admin/includes/file.php', '<?php');
            file_put_contents(ABSPATH . 'wp-admin/includes/plugin.php', '<?php');
        }
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        if (self::$zip && file_exists(self::$zip)) {
            safe_unlink(self::$zip);
        }
        parent::tearDown();
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
        self::$options['artpulse_settings'] = ['github_repo' => 'foo/bar'];
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
        self::$options['artpulse_settings'] = ['github_repo' => 'foo/bar'];
        try {
            UpdatesTab::check_updates();
        } catch (\Exception $e) {
            $this->assertSame('die', $e->getMessage());
        }
        $this->assertSame('ap_check_updates', self::$checked_action);
        $this->assertNotNull(self::$died);
    }

    public function test_run_update_download_error_returns_error_and_redirects(): void
    {
        self::$download_error = new \WP_Error('download_fail', 'download failed');

        $err = UpdatesTab::run_update(true);
        $this->assertInstanceOf(\WP_Error::class, $err);
        $this->assertSame('download_fail', $err->code);

        $_REQUEST['_wpnonce'] = 'valid';
        try {
            UpdatesTab::run_update();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertStringContainsString('ap_update_error=' . urlencode('download failed'), self::$redirect);
        $this->assertNotEmpty(self::$logs);
        $this->assertStringContainsString('download failed', implode(' ', self::$logs));
    }

    public function test_check_updates_remote_error_returns_error_and_redirects(): void
    {
        self::$options['artpulse_settings'] = ['github_repo' => 'foo/bar'];
        self::$remote_error = new \WP_Error('remote_fail', 'remote failed');

        $err = UpdatesTab::check_updates(true);
        $this->assertInstanceOf(\WP_Error::class, $err);
        $this->assertSame('remote_fail', $err->code);

        $_REQUEST['_wpnonce'] = 'valid';
        try {
            UpdatesTab::check_updates();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertStringContainsString('ap_update_error=' . urlencode('remote failed'), self::$redirect);
    }
}
