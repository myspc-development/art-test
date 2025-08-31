<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\WidgetSettingsRestController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\UserDashboardManager;

/**
 * @group restapi
 */
class WidgetSettingsRestControllerTest extends \WP_UnitTestCase {

	private int $uid;

	public function set_up() {
		parent::set_up();
		$this->uid = self::factory()->user->create();
		wp_set_current_user( $this->uid );
		UserDashboardManager::register();
		DashboardWidgetRegistry::register(
			'test-widget',
			'Test',
			'star',
			'desc',
			'__return_null',
			array(
				'settings' => array(
					array(
						'key'     => 'limit',
						'type'    => 'number',
						'default' => 5,
					),
				),
			)
		);
		WidgetSettingsRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_save_and_get_settings(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-settings/test-widget' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
                $req->set_body_params( array( 'settings' => array( 'limit' => 8 ) ) );
                $res       = rest_get_server()->dispatch( $req );
                $post_data = $res->get_data();
                $this->assertSame( 200, $res->get_status() );
                $this->assertArrayHasKey( 'saved', $post_data );
                $this->assertTrue( $post_data['saved'] );
                $this->assertSame( array( 'limit' => 8 ), get_user_meta( $this->uid, 'ap_widget_settings_test-widget', true ) );

		$get  = new \WP_REST_Request( 'GET', '/artpulse/v1/widget-settings/test-widget' );
		$res2 = rest_get_server()->dispatch( $get );
		$data = $res2->get_data();
		$this->assertSame( 200, $res2->get_status() );
		$this->assertSame( array( 'limit' => 8 ), $data['settings'] );
		$this->assertIsArray( $data['schema'] );
	}
}
