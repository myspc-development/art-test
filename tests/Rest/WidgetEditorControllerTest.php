<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group restapi
 */
class WidgetEditorControllerTest extends \WP_UnitTestCase {
        public function set_up(): void {
                parent::set_up();
                \ArtPulse\Rest\WidgetEditorController::register();
                do_action( 'rest_api_init' );
        }

        public function test_roles_endpoint_allows_read_users(): void {
                wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
                $req = new \WP_REST_Request( 'GET', '/artpulse/v1/roles' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 200, $res->get_status() );
        }
}
