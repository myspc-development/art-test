<?php
namespace ArtPulse\Frontend;

if (!function_exists(__NAMESPACE__ . '\is_user_logged_in')) {
function is_user_logged_in(){ return true; }
}
if (!function_exists(__NAMESPACE__ . '\get_current_user_id')) {
function get_current_user_id(){ return 1; }
}
if (!function_exists(__NAMESPACE__ . '\get_user_meta')) {
function get_user_meta($uid,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$user_meta[$uid][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_post_meta')) {
function get_post_meta($id,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$post_meta[$id][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\add_query_arg')) {
function add_query_arg(){ return ''; }
}
if (!function_exists(__NAMESPACE__ . '\paginate_links')) {
function paginate_links($args){ return ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_edit_post_link')) {
function get_edit_post_link($id){ return ''; }
}
if (!function_exists(__NAMESPACE__ . '\sanitize_key')) {
function sanitize_key($key){ return $key; }
}
if (!function_exists(__NAMESPACE__ . '\selected')) {
function selected($val,$cmp,$echo=true){ return $val==$cmp ? 'selected' : ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_terms')) {
function get_terms($tax,$args){ return []; }
}
if (!function_exists(__NAMESPACE__ . '\wp_create_nonce')) {
function wp_create_nonce($action){ return 'nonce'; }
}
if (!function_exists(__NAMESPACE__ . '\esc_html')) {
function esc_html($t){ return $t; }
}
if (!function_exists(__NAMESPACE__ . '\esc_attr')) {
function esc_attr($t){ return $t; }
}
if (!function_exists(__NAMESPACE__ . '\esc_url')) {
function esc_url($t){ return $t; }
}
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$caps[$cap] ?? false; }
}
class WP_Query{ public array $posts=[]; public $max_num_pages=1; public function __construct($a){} }

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

class OrganizationDashboardShortcodeTest extends TestCase
{
    public static array $user_meta = [];
    public static array $post_meta = [];
    public static array $caps = [];

    protected function setUp(): void
    {
        self::$user_meta = [];
        self::$post_meta = [];
        self::$caps = [];
    }

    public function test_dashboard_renders_grid(): void
    {
        self::$user_meta[1]['ap_organization_id'] = 10;
        $html = OrganizationDashboardShortcode::render([]);
        $this->assertStringContainsString('ap-dashboard-grid', $html);
    }

    public function test_analytics_hidden_without_cap(): void
    {
        self::$user_meta[1]['ap_organization_id'] = 10;
        self::$caps['view_analytics'] = false;

        $html = OrganizationDashboardShortcode::render([]);
        $this->assertStringNotContainsString('Organization Analytics', $html);
    }

    public function test_analytics_visible_with_cap(): void
    {
        self::$user_meta[1]['ap_organization_id'] = 10;
        self::$caps['view_analytics'] = true;

        $html = OrganizationDashboardShortcode::render([]);
        $this->assertStringContainsString('Organization Analytics', $html);
    }
}
