<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ImportRestController;
use WP_REST_Request;

/**
 * @group REST
 */
class ImportRestControllerTest extends \WP_UnitTestCase {
    private int $admin_id;

    public function set_up() {
        parent::set_up();
        $this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        ImportRestController::register();
        do_action( 'rest_api_init' );
    }

    public function test_import_creates_posts(): void {
        wp_set_current_user( $this->admin_id );
        $req = new WP_REST_Request( 'POST', '/artpulse/v1/import' );
        $req->set_body_params( array(
            'post_type' => 'artpulse_event',
            'rows'      => array(
                array(
                    'post_title'   => 'My Event',
                    'post_content' => 'Hello',
                ),
            ),
        ) );
        $res = rest_get_server()->dispatch( $req );
        $this->assertSame( 200, $res->get_status() );
        $data = $res->get_data();
        $this->assertCount( 1, $data['created'] );
        $post = get_post( $data['created'][0] );
        $this->assertNotNull( $post );
        $this->assertSame( 'My Event', $post->post_title );
    }

    public function test_import_requires_manage_options(): void {
        $subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $subscriber );
        $req = new WP_REST_Request( 'POST', '/artpulse/v1/import' );
        $req->set_body_params( array(
            'post_type' => 'artpulse_event',
            'rows'      => array(),
        ) );
        $res = rest_get_server()->dispatch( $req );
        $this->assertSame( 403, $res->get_status() );
    }
}
