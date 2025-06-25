<?php
namespace ArtPulse\Core;

// Stub WordPress functions
function is_user_logged_in() { return \ArtPulse\Core\Tests\Stub::$logged_in; }
function current_user_can($cap) { return \ArtPulse\Core\Tests\Stub::$can_view; }
function wp_get_current_user() { return (object)['roles' => \ArtPulse\Core\Tests\Stub::$roles]; }
function get_posts($args) { return []; }
function get_permalink($id) { return '/profile'; }
function home_url($path = '/') { return '/'; }
function esc_html_e($text, $domain = null) { }
function __($text, $domain = null) { return $text; }
function _e($text, $domain = null) { }
function esc_url($url) { return $url; }
function do_shortcode($code) {
    if ($code === '[ap_submit_artist]') {
        return '<form class="ap-artist-submission-form"></form>';
    }
    if ($code === '[ap_submit_organization]') {
        return '<form class="ap-org-submission-form"></form>';
    }
    return '';
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\UserDashboardManager;

class Stub {
    public static $logged_in = true;
    public static $can_view = true;
    public static $roles = [];
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
}
