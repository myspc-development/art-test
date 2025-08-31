<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\RsvpBulkController;

/**
 * @group restapi
 */
class RsvpBulkTest extends \WP_UnitTestCase {
    public function set_up() {
        parent::set_up();
        RsvpBulkController::register();
        do_action( 'rest_api_init' );
    }

    public function test_unauthenticated_request_returns_401(): void {
        wp_set_current_user( 0 );
        $req = new \WP_REST_Request( 'POST', '/ap/v1/rsvp/bulk' );
        $req->set_body_params(
            array(
                'event_id' => 1,
                'ids'      => array( 1 ),
                'status'   => 'going',
            )
        );
        $res = rest_get_server()->dispatch( $req );
        $this->assertSame( 401, $res->get_status() );
    }

    public function test_authenticated_request_returns_200(): void {
        $user = self::factory()->user->create( array( 'role' => 'editor' ) );
        wp_set_current_user( $user );
        $req = new \WP_REST_Request( 'POST', '/ap/v1/rsvp/bulk' );
        $req->set_body_params(
            array(
                'event_id' => 1,
                'ids'      => array( 1, 2 ),
                'status'   => 'cancelled',
            )
        );
        $res = rest_get_server()->dispatch( $req );
        $this->assertSame( 200, $res->get_status() );
        $this->assertSame( array( 'updated' => 2 ), $res->get_data() );
    }
}
