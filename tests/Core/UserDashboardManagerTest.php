<?php
namespace ArtPulse\Core;

// Stub WordPress functions
if (!function_exists(__NAMESPACE__ . '\is_user_logged_in')) {
function is_user_logged_in() { return \ArtPulse\Core\Tests\Stub::$logged_in; }
}
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap) { return \ArtPulse\Core\Tests\Stub::$can_view; }
}
if (!function_exists(__NAMESPACE__ . '\wp_get_current_user')) {
function wp_get_current_user() { return (object)['roles' => \ArtPulse\Core\Tests\Stub::$roles]; }
}
if (!function_exists(__NAMESPACE__ . '\get_posts')) {
function get_posts($args) { return []; }
}
if (!function_exists(__NAMESPACE__ . '\get_permalink')) {
function get_permalink($id) { return '/profile'; }
}
if (!function_exists(__NAMESPACE__ . '\home_url')) {
function home_url($path = '/') { return '/'; }
}
if (!function_exists(__NAMESPACE__ . '\get_current_user_id')) {
function get_current_user_id() { return \ArtPulse\Core\Tests\Stub::$user_id; }
}
if (!function_exists(__NAMESPACE__ . '\get_user_meta')) {
function get_user_meta($uid, $key, $single = false) {
    return \ArtPulse\Core\Tests\Stub::$meta[$key] ?? [];
}
}
if (!function_exists(__NAMESPACE__ . '\get_post')) {
function get_post($id) {
    return (object)['ID' => $id, 'post_title' => 'Post ' . $id];
}
}
if (!function_exists(__NAMESPACE__ . '\esc_url')) {
function esc_url($url) { return $url; }
}
if (!function_exists(__NAMESPACE__ . '\do_shortcode')) {
function do_shortcode($code) {
    if ($code === '[ap_submit_artist]') {
        return '<form class="ap-artist-submission-form"></form>';
    }
}
    if ($code === '[ap_submit_organization]') {
        return '<form class="ap-org-submission-form"></form>';
    }
    return '';
}
if (!function_exists(__NAMESPACE__ . '\locate_template')) {
function locate_template($template) { return ''; }
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\UserDashboardManager;

class Stub {
    public static $logged_in = true;
    public static $can_view = true;
    public static $roles = [];
    public static $user_id = 1;
    public static $meta = [];
}

class UserDashboardManagerTest extends TestCase
{
    public function test_member_dashboard_sections() {
        Stub::$roles = ['member'];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringNotContainsString('id="next-payment"', $html);
        $this->assertStringNotContainsString('id="ap-user-content"', $html);
        $this->assertStringContainsString('id="ap-dashboard-notifications"', $html);
        $this->assertStringContainsString('id="ap-membership-actions"', $html);
    }

    public function test_artist_dashboard_sections() {
        Stub::$roles = ['artist'];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringNotContainsString('id="next-payment"', $html);
        $this->assertStringContainsString('id="ap-user-content"', $html);
        $this->assertStringContainsString('id="ap-dashboard-notifications"', $html);
        $this->assertStringContainsString('id="ap-membership-actions"', $html);
    }

    public function test_org_dashboard_sections() {
        Stub::$roles = ['organization'];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringContainsString('id="next-payment"', $html);
        $this->assertStringContainsString('id="ap-user-content"', $html);
        $this->assertStringContainsString('id="ap-dashboard-notifications"', $html);
        $this->assertStringContainsString('id="ap-membership-actions"', $html);
    }

    public function test_admin_dashboard_sections() {
        Stub::$roles = ['administrator'];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringContainsString('id="next-payment"', $html);
        $this->assertStringContainsString('id="ap-user-content"', $html);
        $this->assertStringContainsString('id="ap-dashboard-notifications"', $html);
        $this->assertStringContainsString('id="ap-membership-actions"', $html);
    }

    public function test_forms_render_when_enabled() {
        Stub::$roles = ['member'];
        $html = UserDashboardManager::renderDashboard(['show_forms' => true]);
        $this->assertStringContainsString('ap-artist-submission-form', $html);
        $this->assertStringContainsString('ap-org-submission-form', $html);
    }

    public function test_support_history_section_shown_when_history_exists() {
        Stub::$roles = ['member'];
        Stub::$meta = ['ap_support_history' => [1]];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringContainsString('id="support-history"', $html);
    }

    public function test_badges_render_when_meta_exists() {
        Stub::$roles = ['member'];
        Stub::$meta = ['user_badges' => ['gold']];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringContainsString('class="ap-badges"', $html);
    }

    public function test_onboarding_banner_shown_when_not_completed() {
        Stub::$roles = ['artist'];
        Stub::$meta = [];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringContainsString('id="ap-onboarding-banner"', $html);
    }

    public function test_onboarding_template_when_query_set() {
        Stub::$roles = ['artist'];
        Stub::$meta = [];
        $_GET['onboarding'] = '1';
        $html = UserDashboardManager::renderDashboard([]);
        unset($_GET['onboarding']);
        $this->assertStringContainsString('id="ap-onboarding-next"', $html);
    }

    public function test_no_onboarding_when_completed() {
        Stub::$roles = ['artist'];
        Stub::$meta = ['ap_onboarding_completed' => 1];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringNotContainsString('ap-onboarding-banner', $html);
    }

    public function test_no_banner_when_tour_completed() {
        Stub::$roles = ['artist'];
        Stub::$meta = ['ap_dashboard_tour_complete' => 1];
        $html = UserDashboardManager::renderDashboard([]);
        $this->assertStringNotContainsString('ap-onboarding-banner', $html);
    }

    protected function tearDown(): void {
        $_GET = [];
        Stub::$logged_in = true;
        Stub::$can_view = true;
        Stub::$roles = [];
        Stub::$user_id = 1;
        Stub::$meta = [];
        parent::tearDown();
    }
}
