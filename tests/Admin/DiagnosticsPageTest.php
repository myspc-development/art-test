<?php
namespace ArtPulse\Admin\Tests;

use WP_Ajax_UnitTestCase;
use ArtPulse\Tests\AjaxTestHelper;

/**

 * @group ADMIN
 */

class DiagnosticsPageTest extends WP_Ajax_UnitTestCase {

	use AjaxTestHelper;

	public function set_up(): void {
		parent::set_up();
	}

	public function tear_down(): void {
		$this->reset_superglobals();
		parent::tear_down();
	}

	public function test_registers_diagnostics_admin_page(): void {
		// Trigger the admin_menu hook to register menu pages.
		do_action( 'admin_menu' );

		// Ensure the helper function is available.
		if ( ! function_exists( 'get_admin_page_parent' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$parent = get_admin_page_parent( 'ap-diagnostics' );
		$this->assertSame( 'admin.php', $parent );
	}

	public function test_ajax_diagnostics_endpoint(): void {
		// Authenticate as an administrator.
		$user_id = $this->make_admin_user();
		wp_set_current_user( $user_id );

		// Provide a valid nonce for check_ajax_referer.
		$this->set_nonce( 'ap_ajax_test', 'nonce' );

		// Execute the AJAX action.
		$this->_handleAjax( 'ap_ajax_test' );

		$response = json_decode( $this->_last_response, true );
		$this->assertTrue( $response['success'] );
		$this->assertSame(
			'AJAX is working, nonce is valid, and you are authenticated.',
			$response['data']['message'] ?? ''
		);
	}
}
