<?php
namespace ArtPulse\Core;

// Stubs for WordPress functions used by AdminAccessManager
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return \ArtPulse\Core\Tests\AdminAccessManagerTest::$caps[ $cap ] ?? false;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return \ArtPulse\Core\Tests\AdminAccessManagerTest::$is_logged_in;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_doing_ajax' ) ) {
	function wp_doing_ajax() {
		return \ArtPulse\Core\Tests\AdminAccessManagerTest::$doing_ajax;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://site.test' . $path;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_safe_redirect' ) ) {
	function wp_safe_redirect( $url ) {
			throw new \Exception( 'redirect:' . $url );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_get_current_user' ) ) {
	function wp_get_current_user() {
			return \ArtPulse\Core\Tests\AdminAccessManagerTest::$current_user;
	}
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\AdminAccessManager;
use function Patchwork\redefine;
use function Patchwork\restore;

/**

 * @group CORE
 */

class AdminAccessManagerTest extends TestCase {

	public static array $caps         = array();
	public static bool $admin_enabled = false;
		private $patchHandle;
	public static bool $is_logged_in = true;
	public static bool $doing_ajax   = false;
	public static $current_user;

	protected function setUp(): void {
				self::$caps          = array();
				self::$admin_enabled = false;
				self::$is_logged_in  = true;
				self::$doing_ajax    = false;
				self::$current_user  = (object) array( 'roles' => array( 'member' ) );
				$_GET                = array();
				$this->patchHandle   = redefine( '\\ArtPulse\\Helpers\\GlobalHelpers::wpAdminAccessEnabled', fn() => self::$admin_enabled );
	}

	public function test_hide_admin_bar_for_non_admin(): void {
		$this->assertFalse( AdminAccessManager::maybe_hide_admin_bar( true ) );
	}

	public function test_show_admin_bar_with_manage_options(): void {
		self::$caps = array( 'manage_options' => true );
		$this->assertTrue( AdminAccessManager::maybe_hide_admin_bar( true ) );
	}

	public function test_show_admin_bar_with_view_wp_admin(): void {
		self::$caps = array( 'view_wp_admin' => true );
		$this->assertTrue( AdminAccessManager::maybe_hide_admin_bar( true ) );
	}

	public function test_redirects_non_admin_users(): void {
		try {
				AdminAccessManager::maybe_redirect_admin();
				$this->fail( 'Expected redirect' );
		} catch ( \Exception $e ) {
				$this->assertSame( 'redirect:https://site.test/dashboard/user', $e->getMessage() );
		}
	}

	public function test_redirects_artist_users(): void {
			self::$current_user = (object) array( 'roles' => array( 'artist' ) );
		try {
				AdminAccessManager::maybe_redirect_admin();
				$this->fail( 'Expected redirect' );
		} catch ( \Exception $e ) {
					$this->assertSame( 'redirect:https://site.test/dashboard/artist', $e->getMessage() );
		}
	}

	public function test_redirects_org_users(): void {
			self::$current_user = (object) array( 'roles' => array( 'organization' ) );
		try {
				AdminAccessManager::maybe_redirect_admin();
				$this->fail( 'Expected redirect' );
		} catch ( \Exception $e ) {
					$this->assertSame( 'redirect:https://site.test/dashboard/org', $e->getMessage() );
		}
	}

	public function test_allows_admin_users(): void {
			self::$caps = array( 'manage_options' => true );
		try {
				AdminAccessManager::maybe_redirect_admin();
		} catch ( \Exception $e ) {
				$this->fail( 'Unexpected redirect: ' . $e->getMessage() );
		}
			$this->assertTrue( true ); // If we reached here, no redirect occurred
	}

	public function test_allows_dashboard_role_page_without_redirect(): void {
			$_GET['page'] = 'dashboard-role';
		try {
				AdminAccessManager::maybe_redirect_admin();
		} catch ( \Exception $e ) {
					$this->fail( 'Unexpected redirect: ' . $e->getMessage() );
		}
			$this->assertTrue( true ); // No redirect should occur for dashboard-role page
	}

	protected function tearDown(): void {
			restore( $this->patchHandle );
			parent::tearDown();
	}
}
