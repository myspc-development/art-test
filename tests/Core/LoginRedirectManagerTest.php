<?php
namespace ArtPulse\Core;

// Simple stubs for WordPress functions used by LoginRedirectManager

if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap) {
    return \ArtPulse\Core\Tests\LoginRedirectManagerTest::$caps[$cap] ?? false;
}
}
if (!function_exists(__NAMESPACE__ . '\ap_wp_admin_access_enabled')) {
function ap_wp_admin_access_enabled() {
    return \ArtPulse\Core\Tests\LoginRedirectManagerTest::$admin_enabled;
}
}
if (!function_exists(__NAMESPACE__ . '\home_url')) {
function home_url($path = '') { return 'https://site.test' . $path; }
}
if (!function_exists(__NAMESPACE__ . '\is_wp_error')) {
function is_wp_error($thing) { return $thing instanceof WP_Error; }
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\LoginRedirectManager;
use WP_Error;

class LoginRedirectManagerTest extends TestCase {
    public static array $caps = [];
    public static bool $admin_enabled = false;

    protected function setUp(): void {
        self::$caps = [];
        self::$admin_enabled = false;
    }

    private function runRedirect(object $user): string {
        return LoginRedirectManager::handle('/default', '', $user);
    }

    public function test_member_redirects_to_dashboard(): void {
        $user = (object)['roles' => ['member']];
        $redirect = $this->runRedirect($user);
        $this->assertSame('https://site.test/dashboard', $redirect);
    }

    public function test_artist_redirects_to_dashboard(): void {
        $user = (object)['roles' => ['artist']];
        $redirect = $this->runRedirect($user);
        $this->assertSame('https://site.test/dashboard', $redirect);
    }

    public function test_org_redirects_to_dashboard(): void {
        $user = (object)['roles' => ['organization']];
        $redirect = $this->runRedirect($user);
        $this->assertSame('https://site.test/dashboard', $redirect);
    }

    public function test_wp_admin_cap_returns_default(): void {
        self::$caps = ['view_wp_admin' => true];
        $user = (object)['roles' => ['member']];
        $redirect = $this->runRedirect($user);
        $this->assertSame('/default', $redirect);
    }

    public function test_error_user_returns_default(): void {
        $err = new WP_Error('failed', 'Bad');
        $redirect = LoginRedirectManager::handle('/default', '', $err);
        $this->assertSame('/default', $redirect);
    }
}
