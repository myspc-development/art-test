<?php
namespace {

// Stub WordPress functions and constants
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function remove_menu_page($slug) {}
function add_menu_page(...$args) {}
function current_user_can($cap) { return true; }
function get_transient($key) { return \ArtPulse\Admin\Tests\Stub::$transients[$key] ?? false; }
function set_transient($key, $value, $expire = 0) { \ArtPulse\Admin\Tests\Stub::$transients[$key] = $value; return true; }
function delete_transient($key) { unset(\ArtPulse\Admin\Tests\Stub::$transients[$key]); return true; }
function get_posts($args) { return \ArtPulse\Admin\Tests\Stub::get_posts($args); }
function wp_is_post_revision($id) { return false; }
function get_post_meta($post_id, $key, $single = false) { return \ArtPulse\Admin\Tests\Stub::get_post_meta($post_id, $key); }
if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}
    class WP_Post {
        public $post_type;
        public $ID;
        public function __construct(string $post_type = 'post', int $ID = 0) {
            $this->post_type = $post_type;
            $this->ID = $ID;
        }
    }
}

namespace ArtPulse\Admin\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\OrgDashboardAdmin;

class Stub {
    public static array $transients = [];
    public static array $posts_return = [];
    public static int $get_posts_calls = 0;
    public static array $post_meta = [];

    public static function reset(): void
    {
        self::$transients = [];
        self::$posts_return = [];
        self::$get_posts_calls = 0;
        self::$post_meta = [];
    }

    public static function get_posts(array $args): array
    {
        self::$get_posts_calls++;
        $type = $args['post_type'] ?? 'post';
        return self::$posts_return[$type] ?? [];
    }

    public static function get_post_meta(int $post_id, string $key)
    {
        return self::$post_meta[$post_id][$key] ?? '';
    }
}

class OrgDashboardAdminTest extends TestCase
{
    protected function setUp(): void
    {
        Stub::reset();
    }

    public function test_get_all_orgs_caches_results(): void
    {
        Stub::$posts_return['artpulse_org'] = [(object)['ID' => 1]];
        $ref = new \ReflectionClass(OrgDashboardAdmin::class);
        $m = $ref->getMethod('get_all_orgs');
        $m->setAccessible(true);

        $first = $m->invoke(null);
        $second = $m->invoke(null);

        $this->assertSame($first, $second);
        $this->assertSame(1, Stub::$get_posts_calls);
    }

    public function test_get_org_posts_caches_results(): void
    {
        Stub::$posts_return['ap_profile_link'] = [(object)['ID' => 2]];
        $ref = new \ReflectionClass(OrgDashboardAdmin::class);
        $m = $ref->getMethod('get_org_posts');
        $m->setAccessible(true);

        $args = ['post_type' => 'ap_profile_link', 'numberposts' => 1];
        $first = $m->invoke(null, 5, 'profile_links', $args);
        $second = $m->invoke(null, 5, 'profile_links', $args);

        $this->assertSame($first, $second);
        $this->assertSame(1, Stub::$get_posts_calls);
    }

    public function test_clear_cache_deletes_transient_on_event_save(): void
    {
        Stub::$transients['ap_dash_profile_links_10'] = ['a'];
        Stub::$transients['ap_dash_artworks_10'] = ['b'];
        Stub::$transients['ap_dash_events_10'] = ['c'];
        Stub::$transients['ap_dash_stats_artworks_10'] = ['d'];
        Stub::$transients['ap_org_metrics_10'] = ['e'];
        Stub::$post_meta[5]['org_id'] = 10;

        $post = new \WP_Post('artpulse_event', 5);
        $ref = new \ReflectionClass(OrgDashboardAdmin::class);
        $m = $ref->getMethod('clear_cache');
        $m->setAccessible(true);

        $m->invoke(null, 5, $post, true);

        $this->assertArrayNotHasKey('ap_dash_profile_links_10', Stub::$transients);
        $this->assertArrayNotHasKey('ap_dash_artworks_10', Stub::$transients);
        $this->assertArrayNotHasKey('ap_dash_events_10', Stub::$transients);
        $this->assertArrayNotHasKey('ap_dash_stats_artworks_10', Stub::$transients);
        $this->assertArrayNotHasKey('ap_org_metrics_10', Stub::$transients);
    }
}
}
