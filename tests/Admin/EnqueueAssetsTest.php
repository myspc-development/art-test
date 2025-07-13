<?php
namespace ArtPulse\Admin;

// Stub WordPress and plugin functions
function wp_enqueue_script(...$args) { \ArtPulse\Admin\Tests\EnqueueAssetsTest::$scripts[] = $args; }
function wp_enqueue_style(...$args) {}
function get_current_screen() { return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$current_screen; }
function plugin_dir_path($file) { return '/'; }
function plugin_dir_url($file) { return '/'; }
function file_exists($path) { return true; }
function admin_url($path = '') { return $path; }
function rest_url($path = '') { return $path; }
function wp_create_nonce($action = '') { return 'nonce'; }
function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$user_meta[$uid][$key] ?? ''; }
function get_posts($args = []) { return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$posts; }
function get_the_terms($post_id, $tax) { return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$terms[$post_id] ?? false; }
function wp_localize_script($handle, $name, $data) {
    \ArtPulse\Admin\Tests\EnqueueAssetsTest::$localized = $data;
    \ArtPulse\Admin\Tests\EnqueueAssetsTest::$localize_calls[] = [$handle, $name, $data];
}
function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$options[$key] ?? $default; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\EnqueueAssetsTest::$options[$key] = $value; }
function wp_roles() { return (object)['roles' => ['administrator' => [], 'subscriber' => []]]; }
function ap_styles_disabled() { return false; }
function wp_script_is($h, $list) { return false; }

namespace ArtPulse\Core;
class Plugin { public static function get_event_submission_url(): string { return '/submit'; } }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\EnqueueAssets;

class EnqueueAssetsTest extends TestCase
{
    public static array $localized = [];
    public static array $user_meta = [];
    public static array $posts = [];
    public static array $terms = [];
    public static array $scripts = [];
    public static array $localize_calls = [];
    public static array $options = [];
    public static $current_screen = null;

    protected function setUp(): void
    {
        self::$localized = [];
        self::$user_meta = [];
        self::$posts = [];
        self::$terms = [];
        self::$scripts = [];
        self::$localize_calls = [];
        self::$options = [];
        self::$current_screen = null;
    }

    public static function add_post(int $id, string $title, string $stage_slug, string $stage_name): void
    {
        self::$posts[] = (object)['ID' => $id, 'post_title' => $title];
        self::$terms[$id] = [(object)['slug' => $stage_slug, 'name' => $stage_name]];
    }

    public function test_localizes_stage_groups(): void
    {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        self::$user_meta[1]['ap_organization_id'] = 5;
        self::add_post(1, 'Art One', 'stage-1', 'Stage 1');
        EnqueueAssets::enqueue_frontend();
        $this->assertArrayHasKey('projectStages', self::$localized);
        $this->assertSame('Stage 1', self::$localized['projectStages'][0]['name']);
    }

    public function test_block_editor_scripts_enqueue_when_screen_available(): void
    {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        self::$current_screen = (object)['id' => 'edit-artpulse_event'];
        EnqueueAssets::enqueue_block_editor_assets();
        $handles = array_map(fn($a) => $a[0] ?? '', self::$scripts);
        $this->assertContains('ap-event-gallery', $handles);
    }

    public function test_exits_gracefully_without_screen(): void
    {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        self::$current_screen = null;
        EnqueueAssets::enqueue_block_editor_assets();
        $this->assertSame([], self::$scripts);
    }

    public function test_dashboard_widgets_scripts_and_localization(): void
    {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        self::$current_screen = (object)[
            'id' => 'artpulse-settings_page_ap-dashboard-widgets',
            'base' => 'artpulse-settings_page_ap-dashboard-widgets',
        ];
        self::$options['ap_dashboard_widget_config'] = ['administrator' => ['foo']];
        EnqueueAssets::enqueue_admin();

        $handles = array_map(fn($a) => $a[0] ?? '', self::$scripts);
        $this->assertContains('sortablejs', $handles);
        $this->assertContains('ap-dashboard-widgets-editor', $handles);

        $this->assertNotEmpty(self::$localize_calls);
        [$handle, $name, $data] = self::$localize_calls[0];
        $this->assertSame('ap-dashboard-widgets-editor', $handle);
        $this->assertSame('APDashboardWidgetsEditor', $name);
        $this->assertArrayHasKey('widgets', $data);
        $this->assertSame(['administrator' => ['foo']], $data['config']);
        $this->assertSame('nonce', $data['nonce']);
        $this->assertSame('admin-ajax.php', $data['ajaxUrl']);
    }

    public function test_dashboard_widgets_config_fallback_created(): void
    {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __FILE__);
        }
        self::$current_screen = (object)[
            'id'   => 'artpulse-settings_page_ap-dashboard-widgets',
            'base' => 'artpulse-settings_page_ap-dashboard-widgets',
        ];

        EnqueueAssets::enqueue_admin();

        $expected_ids = array_column(\ArtPulse\Core\DashboardWidgetRegistry::get_definitions(), 'id');
        $expected     = [];
        foreach (wp_roles()->roles as $role_key => $data) {
            $expected[$role_key] = $expected_ids;
        }

        $this->assertSame($expected, self::$options['ap_dashboard_widget_config']);
        [$handle, $name, $data] = self::$localize_calls[0];
        $this->assertSame($expected, $data['config']);
    }
}
