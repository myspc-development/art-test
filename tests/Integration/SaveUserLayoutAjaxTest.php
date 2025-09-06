<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;

/**

 * @group INTEGRATION
 */

class SaveUserLayoutAjaxTest extends WP_Ajax_UnitTestCase {

	use AjaxTestHelper;

	public function tear_down(): void {
		$this->reset_superglobals();
		parent::tear_down();
	}
	public function test_fails_without_nonce(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		try {
			$this->_handleAjax( 'ap_save_user_layout' );
			$this->fail( 'Expected failure for missing nonce' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}
	}

	public function test_fails_without_capability(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'ID', $user_id );
		$user->remove_cap( 'read' );
		wp_set_current_user( $user_id );

		$this->set_nonce( 'ap_save_user_layout', 'nonce' );

		try {
			$this->_handleAjax( 'ap_save_user_layout' );
		} catch ( \WPAjaxDieStopException $e ) {
			$resp = json_decode( $this->_last_response, true );
			$this->assertFalse( $resp['success'] );
			$this->assertSame( 'Forbidden', $resp['data']['message'] );
		}
	}

	public function test_succeeds_with_nonce_and_capability(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->set_nonce( 'ap_save_user_layout', 'nonce' );
		$_POST['layout'] = wp_json_encode(
			array(
				array(
					'id'      => 'widget',
					'visible' => true,
				),
			)
		);

		$this->_handleAjax( 'ap_save_user_layout' );
		$resp = json_decode( $this->_last_response, true );
		$this->assertTrue( $resp['success'] );
	}
}
