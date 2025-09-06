<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;

/**

 * @group INTEGRATION
 */

class FeedbackAjaxTest extends WP_Ajax_UnitTestCase {

	use AjaxTestHelper;

	public function tear_down(): void {
		$this->reset_superglobals();
		parent::tear_down();
	}
	public function test_submission_fails_without_nonce(): void {
			$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
			$user    = get_user_by( 'ID', $user_id );
			$user->add_cap( 'ap_submit_feedback' );
			wp_set_current_user( $user_id );
			$_POST['description'] = 'Test';

		try {
			$this->_handleAjax( 'ap_submit_feedback' );
			$this->fail( 'Expected missing nonce failure' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}
	}

	public function test_submission_fails_without_capability(): void {
			$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
			wp_set_current_user( $user_id );
			$nonce                = wp_create_nonce( 'ap_feedback_nonce' );
			$_POST['nonce']       = $nonce;
			$_REQUEST['nonce']    = $nonce;
			$_POST['description'] = 'Test';

		try {
			$this->_handleAjax( 'ap_submit_feedback' );
		} catch ( \WPAjaxDieStopException $e ) {
						$resp = json_decode( $this->_last_response, true );
						$this->assertFalse( $resp['success'] );
						$this->assertFalse( $resp['data'] );
		}
	}

	public function test_submission_succeeds_with_nonce_and_capability(): void {
			$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
			$user    = get_user_by( 'ID', $user_id );
			$user->add_cap( 'ap_submit_feedback' );
			wp_set_current_user( $user_id );
			$nonce                = wp_create_nonce( 'ap_feedback_nonce' );
			$_POST['nonce']       = $nonce;
			$_REQUEST['nonce']    = $nonce;
			$_POST['description'] = 'Great plugin';

		$this->_handleAjax( 'ap_submit_feedback' );
		$resp = json_decode( $this->_last_response, true );
		$this->assertTrue( $resp['success'] );
	}
}
