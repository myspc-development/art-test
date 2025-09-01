<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

if ( ! function_exists( __NAMESPACE__ . '\\wp_insert_post' ) ) {
	function wp_insert_post( $arr ) {
		return 123; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_set_post_terms' ) ) {
	function wp_set_post_terms( $id, $terms, $tax ) {
		\ArtPulse\Frontend\Tests\EventServiceTest::$terms = array( $id, $terms, $tax ); }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_posts' ) ) {
	function get_posts( $args = array() ) {
		return \ArtPulse\Frontend\Tests\EventServiceTest::$user_org_posts; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
		return \ArtPulse\Frontend\Tests\EventServiceTest::$user_meta; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\EventService;

/**

 * @group frontend

 */

class EventServiceTest extends TestCase {
	public static array $terms          = array();
	public static array $user_org_posts = array();
	public static int $user_meta        = 0;

	protected function setUp(): void {
		\ArtPulse\Frontend\StubState::reset();
		self::$terms          = array();
		self::$user_org_posts = array( (object) array( 'ID' => 5 ) );
		self::$user_meta      = 5;
	}

	public function test_missing_title_returns_error(): void {
		$result = EventService::create_event(
			array(
				'date'   => '2024-01-01',
				'org_id' => 5,
			),
			1
		);
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_successful_creation_updates_meta_and_terms(): void {
		$data   = array(
			'title'      => 'Event',
			'date'       => '2024-01-01',
			'org_id'     => 5,
			'event_type' => 7,
		);
		$result = EventService::create_event( $data, 1 );
		$this->assertSame( 123, $result );
		$found = false;
		foreach ( \ArtPulse\Frontend\StubState::$meta_log as $args ) {
			if ( $args[1] === '_ap_event_date' && $args[2] === '2024-01-01' ) {
				$found = true;
			}
		}
		$this->assertTrue( $found );
		$this->assertSame( array( 123, array( 7 ), 'event_type' ), self::$terms );
	}
}
