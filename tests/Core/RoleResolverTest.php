<?php
namespace {
	require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Core\Tests {
	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\RoleResolver;
	use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group CORE

	 */

	class RoleResolverTest extends TestCase {
		protected function setUp(): void {
			parent::setUp();
			MockStorage::$users         = array();
			MockStorage::$current_roles = array();
		}

		public function test_resolves_member_account(): void {
			MockStorage::$users[1] = (object) array( 'roles' => array( 'member' ) );
			$this->assertSame( 'member', RoleResolver::resolve( 1 ) );
		}

		public function test_resolves_artist_account(): void {
			MockStorage::$users[2] = (object) array( 'roles' => array( 'artist' ) );
			$this->assertSame( 'artist', RoleResolver::resolve( 2 ) );
		}

		public function test_resolves_organization_account(): void {
			MockStorage::$users[3] = (object) array( 'roles' => array( 'organization' ) );
			$this->assertSame( 'organization', RoleResolver::resolve( 3 ) );
		}

		public function test_resolves_administrator_account(): void {
			MockStorage::$users[4] = (object) array( 'roles' => array( 'administrator' ) );
			$this->assertSame( 'organization', RoleResolver::resolve( 4 ) );
		}

		public function test_admin_preview_overrides_role(): void {
			MockStorage::$users[5]      = (object) array( 'roles' => array( 'administrator' ) );
			MockStorage::$current_roles = array( 'manage_options' );
			$_GET['ap_preview_role']    = 'artist';
			$_GET['ap_preview_nonce']   = wp_create_nonce( 'ap_preview' );
			$this->assertSame( 'artist', RoleResolver::resolve( 5 ) );
			unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );
		}

		public function test_admin_preview_requires_nonce(): void {
			MockStorage::$users[6]      = (object) array( 'roles' => array( 'administrator' ) );
			MockStorage::$current_roles = array( 'manage_options' );
			$_GET['ap_preview_role']    = 'artist';
			$this->assertSame( 'organization', RoleResolver::resolve( 6 ) );
			unset( $_GET['ap_preview_role'] );
		}

		public function test_non_admin_cannot_preview_role(): void {
			MockStorage::$users[7]    = (object) array( 'roles' => array( 'member' ) );
			$_GET['ap_preview_role']  = 'artist';
			$_GET['ap_preview_nonce'] = wp_create_nonce( 'ap_preview' );
			$this->assertSame( 'member', RoleResolver::resolve( 7 ) );
			unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );
		}

		public function test_invalid_preview_is_ignored(): void {
			MockStorage::$users[8]      = (object) array( 'roles' => array( 'administrator' ) );
			MockStorage::$current_roles = array( 'manage_options' );
			$_GET['ap_preview_role']    = 'invalid';
			$_GET['ap_preview_nonce']   = wp_create_nonce( 'ap_preview' );
			$this->assertSame( 'organization', RoleResolver::resolve( 8 ) );
			unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );
		}
	}
}
