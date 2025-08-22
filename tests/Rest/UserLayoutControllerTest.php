<?php
/**
 * @group rest
 */
if (!class_exists('WP_UnitTestCase')) {
    class UserLayoutControllerTest extends PHPUnit\Framework\TestCase {
        public function test_skip() {
            $this->markTestSkipped('WordPress not available');
        }
    }
    return;
}
class UserLayoutControllerTest extends WP_UnitTestCase {
    protected $user_id;

    public function set_up(): void {
        parent::set_up();
        $this->user_id = self::factory()->user()->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $this->user_id );
        global $wp_rest_server;
        $wp_rest_server = $wp_rest_server ?: rest_get_server();
        do_action( 'rest_api_init' );
    }

    public function test_get_layout_defaults_to_preset_when_empty() {
        $response = rest_do_request( new WP_REST_Request( 'GET', '/artpulse/v1/user/layout' ) );
        $this->assertSame( 200, $response->get_status() );
        $data = $response->get_data();
        $this->assertArrayHasKey( 'layout', $data );
        $this->assertIsArray( $data['layout'] );
    }

    public function test_post_layout_persists_for_user() {
        $req = new WP_REST_Request( 'POST', '/artpulse/v1/user/layout' );
        $req->set_body_params( [
            'role'   => 'artist',
            'layout' => [ 'upcomingEvents', 'sales', 'tasks' ],
        ] );
        // If your controller checks a nonce header, add it here:
        // $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

        $response = rest_do_request( $req );
        $this->assertSame( 200, $response->get_status(), print_r( $response->get_data(), true ) );
        $data = $response->get_data();
        $this->assertSame( [ 'upcomingEvents', 'sales', 'tasks' ], $data['layout'] );

        // Fetch again to confirm persistence
        $get = rest_do_request( new WP_REST_Request( 'GET', '/artpulse/v1/user/layout?role=artist' ) );
        $this->assertSame( 200, $get->get_status() );
        $this->assertSame( [ 'upcomingEvents', 'sales', 'tasks' ], $get->get_data()['layout'] );
    }
}
