<?php
namespace ArtPulse\Admin;

// WordPress stubs for unit testing
if ( ! function_exists( __NAMESPACE__ . '\update_user_meta' ) ) {
	function update_user_meta( $uid, $key, $value ) {
		\ArtPulse\Admin\Tests\WidgetManagerTest::$meta[ $uid ][ $key ] = $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
		return \ArtPulse\Admin\Tests\WidgetManagerTest::$meta[ $uid ][ $key ] ?? ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\delete_user_meta' ) ) {
	function delete_user_meta( $uid, $key ) {
		unset( \ArtPulse\Admin\Tests\WidgetManagerTest::$meta[ $uid ][ $key ] ); }
}
if ( ! function_exists( __NAMESPACE__ . '\get_userdata' ) ) {
	function get_userdata( $uid ) {
		return \ArtPulse\Admin\Tests\WidgetManagerTest::$users[ $uid ] ?? null; }
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_]/i', '', strtolower( $key ) ); }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group unit

 */

class WidgetManagerTest extends TestCase {

	public static array $meta  = array();
	public static array $users = array();

	protected function setUp(): void {
		self::$meta  = array();
		self::$users = array();
                DashboardWidgetRegistry::register( 'one', 'One', '', '', '__return_null' );
                DashboardWidgetRegistry::register( 'two', 'Two', '', '', '__return_null' );
	}

	public function test_save_user_layout_alias(): void {
                UserLayoutManager::save_user_layout( 1, array( array( 'id' => 'two' ), array( 'id' => 'one' ), array( 'id' => 'two' ) ) );
                $expected = array(
                        array(
                                'id'      => 'widget_two',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_one',
                                'visible' => true,
                        ),
                );
		$this->assertSame( $expected, self::$meta[1][ UserLayoutManager::META_KEY ] );
	}

	public function test_reset_user_layout_removes_meta(): void {
                self::$meta[1][ UserLayoutManager::META_KEY ]     = array( array( 'id' => 'widget_one' ) );
                self::$meta[1][ UserLayoutManager::VIS_META_KEY ] = array( 'widget_one' => true );
		UserLayoutManager::reset_user_layout( 1 );
		$this->assertArrayNotHasKey( UserLayoutManager::META_KEY, self::$meta[1] ?? array() );
		$this->assertArrayNotHasKey( UserLayoutManager::VIS_META_KEY, self::$meta[1] ?? array() );
	}

	public function test_get_primary_role_falls_back_to_subscriber(): void {
		self::$users[5] = (object) array( 'roles' => array() );
		$this->assertSame( 'subscriber', UserLayoutManager::get_primary_role( 5 ) );
	}
}
