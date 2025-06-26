<?php
namespace ArtPulse\Admin;

// Stub WordPress and plugin functions
function wp_enqueue_script(...$args) {}
function wp_enqueue_style(...$args) {}
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
function wp_localize_script($handle, $name, $data) { \ArtPulse\Admin\Tests\EnqueueAssetsTest::$localized = $data; }
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

    protected function setUp(): void
    {
        self::$localized = [];
        self::$user_meta = [];
        self::$posts = [];
        self::$terms = [];
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
}
