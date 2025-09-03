<?php
class Auth_Test extends WP_UnitTestCase {
	protected $login_redirect_cb;

	public function tearDown(): void {
		if ( $this->login_redirect_cb ) {
			remove_filter( 'login_redirect', $this->login_redirect_cb, 10 );
			$this->login_redirect_cb = null;
		}
		parent::tearDown();
	}

	public function test_login_redirect_filter() {
		$this->login_redirect_cb = function ( $redirect_to ) {
			return '/dashboard';
		};
		add_filter( 'login_redirect', $this->login_redirect_cb, 10 );

		$user_id = self::factory()->user->create();
		$user    = get_user_by( 'ID', $user_id );

		$result = apply_filters( 'login_redirect', '/wp-admin', '', $user );
		$this->assertSame( '/dashboard', $result );
	}

	public function test_login_redirect_filter_removed() {
		$user_id = self::factory()->user->create();
		$user    = get_user_by( 'ID', $user_id );

		$result = apply_filters( 'login_redirect', '/wp-admin', '', $user );
		$this->assertSame( '/wp-admin', $result );
	}

	public function test_require_login_and_cap_unauthenticated_returns_403() {
		wp_set_current_user( 0 );
		$cb     = \ArtPulse\Rest\Util\Auth::require_login_and_cap( 'read' );
		$result = $cb( new \WP_REST_Request( 'GET', '/' ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 403, $result->get_error_data()['status'] ); // 403 keeps clients from prompting for auth
	}

	public function test_require_login_and_cap_handles_various_capabilities() {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$cb = \ArtPulse\Rest\Util\Auth::require_login_and_cap( array( 'read', 'edit_posts' ) );
		$this->assertTrue( $cb( new \WP_REST_Request( 'GET', '/' ) ) );

		$cb  = \ArtPulse\Rest\Util\Auth::require_login_and_cap( array( 'read', 'manage_options' ) );
		$res = $cb( new \WP_REST_Request( 'GET', '/' ) );
		$this->assertInstanceOf( WP_Error::class, $res );
		$this->assertSame( 403, $res->get_error_data()['status'] );

		$cb      = \ArtPulse\Rest\Util\Auth::require_login_and_cap( fn( $req ) => $req->get_param( 'ok' ) === 'yes' );
		$request = new \WP_REST_Request( 'GET', '/' );
		$request->set_param( 'ok', 'no' );
		$res = $cb( $request );
		$this->assertInstanceOf( WP_Error::class, $res );
		$this->assertSame( 403, $res->get_error_data()['status'] );
		$request->set_param( 'ok', 'yes' );
		$this->assertTrue( $cb( $request ) );
	}
}
