<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\EventService;

/**

 * @group FRONTEND
 */

class EventServiceTest extends TestCase {

	protected function setUp(): void {
			\ArtPulse\Frontend\StubState::reset();
			\ArtPulse\Frontend\StubState::$wp_insert_post_return     = 123;
			\ArtPulse\Frontend\StubState::$get_posts_return          = array( (object) array( 'ID' => 5 ) );
			$GLOBALS['__ap_test_user_meta']                          = array();
			$GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 5;
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
				$this->assertSame( array( 123, array( 7 ), 'event_type' ), \ArtPulse\Frontend\StubState::$wp_set_post_terms_args );
	}
}
