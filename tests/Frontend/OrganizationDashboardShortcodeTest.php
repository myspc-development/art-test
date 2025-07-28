<?php
namespace ArtPulse\Frontend;

function is_user_logged_in(){ return true; }
function get_current_user_id(){ return 1; }
function get_user_meta($uid,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$user_meta[$uid][$key] ?? ''; }
function get_post_meta($id,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$post_meta[$id][$key] ?? ''; }
function add_query_arg(){ return ''; }
function paginate_links($args){ return ''; }
function get_edit_post_link($id){ return ''; }
function sanitize_key($key){ return $key; }
function selected($val,$cmp,$echo=true){ return $val==$cmp ? 'selected' : ''; }
function get_terms($tax,$args){ return []; }
function wp_create_nonce($action){ return 'nonce'; }
function esc_html_e($t,$d=null){}
function _e($t,$d=null){}
function esc_html($t){ return $t; }
function esc_attr($t){ return $t; }
function esc_url($t){ return $t; }
function current_user_can($cap){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$caps[$cap] ?? false; }
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
