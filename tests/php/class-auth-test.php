<?php
class Auth_Test extends WP_UnitTestCase {
    public function test_login_redirect_filter() {
        add_filter( 'login_redirect', function( $redirect_to ) {
            return '/dashboard';
        }, 10 );

        $user_id = self::factory()->user->create();
        $user = get_user_by( 'ID', $user_id );

        $result = apply_filters( 'login_redirect', '/wp-admin', '', $user );
        $this->assertSame( '/dashboard', $result );
    }
}
