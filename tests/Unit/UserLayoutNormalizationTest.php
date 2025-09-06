<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

// Provide minimal WordPress stubs
if ( ! function_exists( __NAMESPACE__ . '\\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
			return UserLayoutNormalizationTest::$meta[ $uid ][ $key ] ?? array();
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\get_userdata' ) ) {
	function get_userdata( $uid ) {
			return null;
	}
}
if ( ! function_exists( '\\__return_null' ) ) {
	function __return_null() {
			return null;
	}
}

class UserLayoutNormalizationTest extends TestCase {
	public static array $meta = array();

	protected function setUp(): void {
			self::$meta = array();
			$ref        = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop       = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
			DashboardWidgetRegistry::register( 'widget_one', 'One', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'widget_two', 'Two', '', '', '__return_null' );
	}

		/**
		 * @group UNIT
		 */
	public function test_user_layout_is_normalized(): void {
			self::$meta[1][ UserLayoutManager::META_KEY ] = array(
				array(
					'id'      => 'Widget_One',
					'visible' => false,
				),
				array(
					'id'      => 'widget_two',
					'visible' => true,
				),
				array(
					'id'      => 'widget_one',
					'visible' => true,
				),
				array(
					'id'      => 'unknown',
					'visible' => true,
				),
			);

			$layout = UserLayoutManager::get_layout_for_user( 1 );

			$expected = array(
				array(
					'id'      => 'widget_one',
					'visible' => false,
				),
				array(
					'id'      => 'widget_two',
					'visible' => true,
				),
			);

			$this->assertSame( $expected, $layout );
	}
}
