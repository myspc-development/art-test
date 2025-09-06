<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardController;

/**

 * @group INTEGRATION
 */

class DashboardRoleSwitchTest extends \WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		if ( ! defined( 'AP_VERBOSE_DEBUG' ) ) {
			define( 'AP_VERBOSE_DEBUG', true );
		}
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public static function roleProvider(): array {
		return array( array( 'member' ), array( 'artist' ), array( 'organization' ) );
	}

	/**
	 * @dataProvider roleProvider
	 */
	public function test_resolver_sets_query_var_and_header( string $role ): void {
		header_remove();
		$_GET['role'] = $role;
		$q            = new \WP_Query();
		DashboardController::resolveRoleIntoQuery( $q );
		$this->assertSame( $role, get_query_var( 'ap_role' ) );
		do_action( 'send_headers' );
		$this->assertContains( 'X-AP-Resolved-Role: ' . $role, headers_list() );
		header_remove();
	}

	public function test_invalid_role_falls_back_to_member(): void {
		$_GET['role'] = 'invalid';
		$q            = new \WP_Query();
		DashboardController::resolveRoleIntoQuery( $q );
		$this->assertSame( 'member', get_query_var( 'ap_role' ) );
	}
}
