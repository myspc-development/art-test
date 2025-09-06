<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group INTEGRATION
 */

class LayoutSaveEndpointAjaxTest extends WP_Ajax_UnitTestCase {

		use AjaxTestHelper;

	public function tear_down(): void {
			$this->reset_superglobals();
			parent::tear_down();
	}

	public function test_requires_login(): void {
			$this->set_nonce( 'ap_dashboard_nonce' );

		try {
				$this->_handleAjax( 'save_dashboard_layout' );
				$this->fail( 'Expected forbidden response' );
		} catch ( \WPAjaxDieStopException $e ) {
				$this->assertSame( 403, http_response_code() );
				$resp = json_decode( $this->_last_response, true );
				$this->assertFalse( $resp['success'] );
				$this->assertNull( $resp['data'] );
		}
	}

	public function test_updates_dashboard_layout_meta(): void {
			$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
			wp_set_current_user( $user_id );

			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', 'strtolower' );

			$this->set_nonce( 'ap_dashboard_nonce' );
			$_POST['layout'] = wp_json_encode(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => true,
					),
				)
			);

			$this->_handleAjax( 'save_dashboard_layout' );
			$resp = json_decode( $this->_last_response, true );
			$this->assertTrue( $resp['success'] );

			$expected = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			);
			$this->assertSame( $expected, get_user_meta( $user_id, 'ap_dashboard_layout', true ) );
	}
}
