<?php
namespace ArtPulse\Integration\Tests;

/**

 * @group integration

 */

class LoginRedirectHookTest extends \WP_UnitTestCase {

	public function test_failed_login_returns_redirect(): void {
		$redirect = apply_filters( 'login_redirect', '/dest', '', new \WP_Error( 'auth_failed', 'failed' ) );
		$this->assertSame( '/dest', $redirect );
	}
}
