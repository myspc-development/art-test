<?php
namespace ArtPulse\Frontend;

if (!function_exists(__NAMESPACE__ . '\check_ajax_referer')) {
function check_ajax_referer($action,$name) {}
}
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap,$id=0){ return true; }
}
if (!function_exists(__NAMESPACE__ . '\get_post_type')) {
function get_post_type($id){ return 'artpulse_event'; }
}
if (!function_exists(__NAMESPACE__ . '\sanitize_text_field')) {
function sanitize_text_field($v){ return is_string($v) ? $v : ''; }
}
if (!function_exists(__NAMESPACE__ . '\sanitize_email')) {
function sanitize_email($v){ return $v; }
}
if (!function_exists(__NAMESPACE__ . '\wp_update_post')) {
function wp_update_post($arr){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$updated = $arr; }
}
if (!function_exists(__NAMESPACE__ . '\update_post_meta')) {
function update_post_meta(...$args){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$meta_updates[] = $args; }
}
if (!function_exists(__NAMESPACE__ . '\get_post_meta')) {
function get_post_meta($id,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$post_meta[$id][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_posts')) {
function get_posts($args=[]){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$passed_args = $args; return \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$posts; }
}
if (!function_exists(__NAMESPACE__ . '\esc_html')) {
function esc_html($t){ return $t; }
}
if (!function_exists(__NAMESPACE__ . '\wp_set_post_terms')) {
function wp_set_post_terms($id,$terms,$tax){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$terms = [$id,$terms,$tax]; }
}
if (!function_exists(__NAMESPACE__ . '\wp_send_json_success')) {
function wp_send_json_success($data){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$json = $data; }
}
if (!function_exists(__NAMESPACE__ . '\wp_send_json_error')) {
function wp_send_json_error($data){ \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$json_error = $data; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

class OrganizationEventAjaxTest extends TestCase
{
    public static array $post_meta = [];
    public static array $posts = [];
    public static array $passed_args = [];
    public static array $meta_updates = [];
    public static array $updated = [];
    public static array $json = [];
    public static $json_error = null;
    public static array $terms = [];

    protected function setUp(): void
    {
        self::$post_meta = [];
        self::$posts = [];
        self::$passed_args = [];
        self::$meta_updates = [];
        self::$updated = [];
        self::$json = [];
        self::$json_error = null;
        self::$terms = [];
        $_POST = [];
    }

    public function test_update_event_returns_html(): void
    {
        self::$post_meta[7]['_ap_event_organization'] = 5;
        self::$posts = [
            (object)['ID' => 7, 'post_title' => 'First'],
            (object)['ID' => 8, 'post_title' => 'Second'],
        ];

        $addr = [ 'country' => 'US', 'state' => 'CA', 'city' => 'LA' ];

        $_POST = [
            'nonce' => 'n',
            'ap_event_id' => 7,
            'ap_event_title' => 'First',
            'ap_event_date' => '2024-01-01',
            'ap_event_start_date' => '',
            'ap_event_end_date' => '',
            'ap_event_location' => '',
            'ap_venue_name' => '',
            'ap_event_street_address' => '',
            'ap_event_country' => '',
            'ap_event_state' => '',
            'ap_event_city' => '',
            'ap_event_postcode' => '',
            'address_components' => json_encode($addr),
            'ap_event_organizer_name' => '',
            'ap_event_organizer_email' => '',
        ];

        OrganizationDashboardShortcode::handle_ajax_update_event();

        $this->assertSame(7, self::$updated['ID'] ?? null);
        $this->assertSame(5, self::$passed_args['meta_value'] ?? null);
        $html = self::$json['updated_list_html'] ?? '';
        $this->assertStringContainsString('First', $html);
        $this->assertStringContainsString('Second', $html);

        $expected_meta = [7, 'address_components', json_encode($addr)];
        $this->assertContains($expected_meta, self::$meta_updates);
    }
}
