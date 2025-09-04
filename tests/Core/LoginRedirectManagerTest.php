<?php
namespace ArtPulse\Core;

// Simple stubs for WordPress functions used by LoginRedirectManager

if ( ! function_exists( __NAMESPACE__ . '\add_filter' ) ) {
        function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
                \ArtPulse\Core\Tests\LoginRedirectManagerTest::$filter = $callback;
        }
}
if ( ! function_exists( __NAMESPACE__ . '\apply_filters' ) ) {
        function apply_filters( $hook, $value, ...$args ) {
                $cb = \ArtPulse\Core\Tests\LoginRedirectManagerTest::$filter;
                return $cb ? $cb( $value, ...$args ) : $value;
        }
}
if ( ! function_exists( __NAMESPACE__ . '\current_user_can' ) ) {
        function current_user_can( $cap ) {
                return \ArtPulse\Core\Tests\LoginRedirectManagerTest::$caps[ $cap ] ?? false;
        }
}
if ( ! function_exists( __NAMESPACE__ . '\ap_wp_admin_access_enabled' ) ) {
        function ap_wp_admin_access_enabled() {
                return \ArtPulse\Core\Tests\LoginRedirectManagerTest::$admin_enabled;
        }
}
if ( ! function_exists( __NAMESPACE__ . '\home_url' ) ) {
        function home_url( $path = '' ) {
                return 'https://site.test' . $path; }
}
if ( ! function_exists( __NAMESPACE__ . '\is_wp_error' ) ) {
        function is_wp_error( $thing ) {
                return $thing instanceof WP_Error; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_validate_redirect' ) ) {
        function wp_validate_redirect( $location, $default = '' ) {
                if ( empty( $location ) ) {
                        return $default;
                }
                $site = home_url();
                if ( strpos( $location, '/' ) === 0 || strpos( $location, $site ) === 0 ) {
                        return $location;
                }
                return $default;
        }
}
if ( ! function_exists( __NAMESPACE__ . '\esc_url_raw' ) ) {
        function esc_url_raw( $url ) { return $url; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_safe_redirect' ) ) {
        function wp_safe_redirect( $url ) {}
}

if ( ! class_exists( __NAMESPACE__ . '\WP_Error' ) ) {
       class WP_Error {}
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\LoginRedirectManager;
use ArtPulse\Core\WP_Error;

/**

 * @group CORE

 */

class LoginRedirectManagerTest extends TestCase {
        public static array $caps         = array();
        public static bool $admin_enabled = false;
        public static $filter             = null;

        protected function setUp(): void {
                self::$caps          = array();
                self::$admin_enabled = false;
                self::$filter        = null;
        }

        private function runRedirect( object $user, string $requested = '' ): string {
                LoginRedirectManager::register();
                return apply_filters( 'login_redirect', '/default', $requested, $user );
        }

        public function test_member_redirects_to_dashboard(): void {
               $user     = (object) array( 'roles' => array( 'member' ) );
               $redirect = $this->runRedirect( $user, 'https://evil.test/phish' );
               $this->assertSame( 'https://site.test/dashboard/user', $redirect );
        }

        public function test_artist_redirects_to_dashboard(): void {
               $user     = (object) array( 'roles' => array( 'artist' ) );
               $redirect = $this->runRedirect( $user, 'https://evil.test/phish' );
               $this->assertSame( 'https://site.test/dashboard/artist', $redirect );
        }

        public function test_org_redirects_to_dashboard(): void {
               $user     = (object) array( 'roles' => array( 'organization' ) );
               $redirect = $this->runRedirect( $user, 'https://evil.test/phish' );
               $this->assertSame( 'https://site.test/dashboard/org', $redirect );
        }

        public function test_safe_redirect_is_used(): void {
               $user     = (object) array( 'roles' => array( 'member' ) );
               $redirect = $this->runRedirect( $user, 'https://site.test/somewhere' );
               $this->assertSame( 'https://site.test/somewhere', $redirect );
        }

	public function test_wp_admin_cap_returns_default(): void {
		self::$caps = array( 'view_wp_admin' => true );
                $user       = (object) array( 'roles' => array( 'member' ) );
                $redirect   = $this->runRedirect( $user, 'https://evil.test/phish' );
                $this->assertSame( '/default', $redirect );
        }

	public function test_error_user_returns_default(): void {
                $err      = new WP_Error( 'failed', 'Bad' );
                $redirect = LoginRedirectManager::handle( '/default', 'https://evil.test/phish', $err );
                $this->assertSame( '/default', $redirect );
        }
}
