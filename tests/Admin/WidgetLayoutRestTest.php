<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Rest\WidgetLayoutController;

/**
 * @group ADMIN
 */
class WidgetLayoutRestTest extends \WP_UnitTestCase {

	private int $uid;

	public function set_up() {
		parent::set_up();
		$this->uid = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->uid );

		WidgetLayoutController::register();
		do_action( 'rest_api_init' );
	}

	public function test_post_saves_layout_with_meta_key(): void {
				$layout = array(
					array( 'id' => 'widget_foo' ),
					array(
						'id'      => 'bar',
						'visible' => false,
					),
				);
				$req    = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-layout' );
				$req->set_body_params( $layout );
				$res = rest_get_server()->dispatch( $req );
				$this->assertSame( 200, $res->get_status() );
				$this->assertSame( $layout, get_user_meta( $this->uid, UserLayoutManager::META_KEY, true ) );
	}
}
