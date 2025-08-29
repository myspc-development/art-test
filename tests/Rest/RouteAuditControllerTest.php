<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group restapi
 */
class RouteAuditControllerTest extends \WP_UnitTestCase {
        public function set_up(): void {
                parent::set_up();
                \ArtPulse\Rest\RouteAudit::register();
                do_action( 'rest_api_init' );
        }

        public function test_subscriber_can_access_audit(): void {
                wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
                $req = new \WP_REST_Request( 'GET', '/ap/v1/routes/audit' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 200, $res->get_status() );
        }
}
