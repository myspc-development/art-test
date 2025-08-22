<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

if (!function_exists(__NAMESPACE__ . '\\wp_insert_post')) {
    function wp_insert_post($arr) { return 123; }
}
if (!function_exists(__NAMESPACE__ . '\\update_post_meta')) {
}
if (!function_exists(__NAMESPACE__ . '\\wp_set_post_terms')) {
    function wp_set_post_terms($id, $terms, $tax) { \ArtPulse\Frontend\Tests\EventServiceTest::$terms = [$id, $terms, $tax]; }
}
if (!function_exists(__NAMESPACE__ . '\\get_posts')) {
    function get_posts($args = []) { return \ArtPulse\Frontend\Tests\EventServiceTest::$user_org_posts; }
}
if (!function_exists(__NAMESPACE__ . '\\get_user_meta')) {
    function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Frontend\Tests\EventServiceTest::$user_meta; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\EventService;

class EventServiceTest extends TestCase {
    public static array $meta_updates = [];
    public static array $terms = [];
    public static array $user_org_posts = [];
    public static int $user_meta = 0;

    protected function setUp(): void {
        self::$meta_updates = [];
        self::$terms = [];
        self::$user_org_posts = [(object)['ID' => 5]];
        self::$user_meta = 5;
    }

    public function test_missing_title_returns_error(): void {
        $result = EventService::create_event([
            'date'   => '2024-01-01',
            'org_id' => 5,
        ], 1);
        $this->assertInstanceOf(\WP_Error::class, $result);
    }

    public function test_successful_creation_updates_meta_and_terms(): void {
        $data = [
            'title'    => 'Event',
            'date'     => '2024-01-01',
            'org_id'   => 5,
            'event_type' => 7,
        ];
        $result = EventService::create_event($data, 1);
        $this->assertSame(123, $result);
        $found = false;
        foreach (self::$meta_updates as $args) {
            if ($args[1] === '_ap_event_date' && $args[2] === '2024-01-01') {
                $found = true;
            }
        }
        $this->assertTrue($found);
        $this->assertSame([123, [7], 'event_type'], self::$terms);
    }
}
