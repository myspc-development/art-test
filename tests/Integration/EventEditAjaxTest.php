<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;

/**

 * @group INTEGRATION

 */

class EventEditAjaxTest extends WP_Ajax_UnitTestCase {

	use AjaxTestHelper;

	public function set_up(): void {
		parent::set_up();
		if ( ! post_type_exists( 'artpulse_event' ) ) {
			register_post_type( 'artpulse_event' );
		}
	}

	private function base_post_data( int $post_id ): array {
		return array(
			'post_id'    => $post_id,
			'title'      => 'Updated',
			'content'    => 'Body',
			'date'       => '2024-01-01',
			'location'   => 'Location',
			'event_type' => 0,
		);
	}

	public function test_save_event_fails_without_nonce(): void {
		$author = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $author );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_author' => $author,
			)
		);
		$_POST   = $this->base_post_data( $post_id );

		try {
			$this->_handleAjax( 'ap_save_event' );
			$this->fail( 'Expected nonce failure' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}
	}

	public function test_save_event_fails_without_capability(): void {
		$author  = self::factory()->user->create( array( 'role' => 'author' ) );
		$other   = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_author' => $author,
			)
		);

		wp_set_current_user( $other );
		$_POST = $this->base_post_data( $post_id );
		$this->set_nonce( 'ap_save_event', 'nonce' );

		try {
			$this->_handleAjax( 'ap_save_event' );
		} catch ( \WPAjaxDieStopException $e ) {
			$resp = json_decode( $this->_last_response, true );
			$this->assertFalse( $resp['success'] );
			$this->assertSame( 'Permission denied.', $resp['data']['message'] );
		}
	}

	public function test_save_event_succeeds_with_nonce_and_capability(): void {
		$author = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $author );
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_author' => $author,
			)
		);

		$_POST = $this->base_post_data( $post_id );
		$this->set_nonce( 'ap_save_event', 'nonce' );

		$this->_handleAjax( 'ap_save_event' );
		$resp = json_decode( $this->_last_response, true );
		$this->assertTrue( $resp['success'] );
	}

	public function tear_down(): void {
		$this->reset_superglobals();
		parent::tear_down();
	}
}
