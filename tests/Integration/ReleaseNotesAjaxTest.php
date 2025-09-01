<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;

/**

 * @group integration

 */

class ReleaseNotesAjaxTest extends WP_Ajax_UnitTestCase {

	use AjaxTestHelper;

	public function tear_down(): void {
		$this->reset_superglobals();
		parent::tear_down();
	}
	public function test_dismiss_fails_without_nonce(): void {
		$user_id = $this->make_admin_user();
		wp_set_current_user( $user_id );

		try {
			$this->_handleAjax( 'ap_dismiss_release_notes' );
			$this->fail( 'Expected missing nonce failure' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}
	}

	public function test_dismiss_fails_without_capability(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->set_nonce( 'ap_dismiss_release_notes', 'nonce' );

		try {
			$this->_handleAjax( 'ap_dismiss_release_notes' );
		} catch ( \WPAjaxDieStopException $e ) {
			$resp = json_decode( $this->_last_response, true );
			$this->assertFalse( $resp['success'] );
			$this->assertSame( 'Forbidden', $resp['data']['message'] );
		}
	}

	public function test_dismiss_succeeds_with_nonce_and_capability(): void {
		$user_id = $this->make_admin_user();
		wp_set_current_user( $user_id );
		$this->set_nonce( 'ap_dismiss_release_notes', 'nonce' );

		$this->_handleAjax( 'ap_dismiss_release_notes' );
		$resp = json_decode( $this->_last_response, true );
		$this->assertTrue( $resp['success'] );
	}
}
