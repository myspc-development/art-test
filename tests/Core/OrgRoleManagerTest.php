<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\OrgRoleManager;

if ( ! function_exists( __NAMESPACE__ . '\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
		return OrgRoleManagerTest::$meta[ $uid ][ $key ] ?? '';
	}
}
if ( ! function_exists( __NAMESPACE__ . '\get_post_meta' ) ) {
	function get_post_meta( $pid, $key, $single = false ) {
		return OrgRoleManagerTest::$post_meta[ $pid ][ $key ] ?? '';
	}
}
if ( ! function_exists( __NAMESPACE__ . '\get_current_user_id' ) ) {
	function get_current_user_id() {
		return OrgRoleManagerTest::$current_id;
	}
}

/**

 * @group CORE

 */

class OrgRoleManagerTest extends TestCase {

	public static array $meta      = array();
	public static array $post_meta = array();
	public static int $current_id  = 1;

	protected function setUp(): void {
		self::$meta       = array();
		self::$post_meta  = array();
		self::$current_id = 1;
	}

	public function test_current_user_can_checks_role(): void {
		self::$meta[1]['ap_organization_id'] = 10;
		self::$meta[1]['ap_org_role']        = 'event_manager';
		$this->assertTrue( OrgRoleManager::current_user_can( 'manage_events', 10 ) );
		$this->assertFalse( OrgRoleManager::current_user_can( 'manage_users', 10 ) );
	}

	public function test_user_can_with_custom_roles(): void {
		self::$meta[1]['ap_organization_id'] = 5;
		self::$meta[1]['ap_org_roles']       = array( 'finance_manager' );
		self::$post_meta[5]['ap_org_roles']  = array(
			'finance_manager' => array(
				'name' => 'Finance Manager',
				'caps' => array( 'manage_finances', 'view_finance' ),
			),
		);
		$this->assertTrue( OrgRoleManager::user_can( 1, 5, 'manage_finances' ) );
		$this->assertFalse( OrgRoleManager::user_can( 1, 5, 'manage_users' ) );
	}
}
