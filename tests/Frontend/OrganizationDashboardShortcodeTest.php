<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

if (!function_exists(__NAMESPACE__ . '\get_user_meta')) {
function get_user_meta($uid,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$user_meta[$uid][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_post_meta')) {
function get_post_meta($id,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$post_meta[$id][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\get_terms')) {
function get_terms($tax,$args){ return []; }
}
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap){ return \ArtPulse\Frontend\Tests\OrganizationDashboardShortcodeTest::$caps[$cap] ?? false; }
}

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
