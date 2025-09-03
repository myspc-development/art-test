<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\WidgetSettingsRestController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\UserDashboardManager;

/**
 * @group REST
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

        public function test_global_settings_requires_manage_cap(): void {
                $req = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-settings/test-widget' );
                $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
                $req->set_param( 'global', 1 );
                $req->set_body_params( array( 'settings' => array( 'limit' => 7 ) ) );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 403, $res->get_status() );
                $this->assertSame( false, get_option( 'ap_widget_settings_test-widget', false ) );

                $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
                wp_set_current_user( $admin );
                $req2 = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-settings/test-widget' );
                $req2->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
                $req2->set_param( 'global', 1 );
                $req2->set_body_params( array( 'settings' => array( 'limit' => 9 ) ) );
                $res2 = rest_get_server()->dispatch( $req2 );
                $this->assertSame( 200, $res2->get_status() );
                $data = $res2->get_data();
                $this->assertTrue( $data['saved'] );
                $this->assertSame( array( 'limit' => 9 ), get_option( 'ap_widget_settings_test-widget' ) );
        }

       public function test_permission_checks_run_even_when_permission_callback_disabled(): void {
               register_rest_route(
                       'artpulse/v1',
                       '/widget-settings/(?P<id>[a-z0-9_-]+)',
                       array(
                               array(
                                       'methods'            => 'POST',
                                       'callback'           => array( WidgetSettingsRestController::class, 'save_settings' ),
                                       'permission_callback' => '__return_true',
                               ),
                       ),
                       true
               );

               $req = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-settings/test-widget' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $req->set_param( 'global', 1 );
               $req->set_body_params( array( 'settings' => array( 'limit' => 6 ) ) );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 403, $res->get_status() );
               $this->assertSame( false, get_option( 'ap_widget_settings_test-widget', false ) );
       }

       public function test_requires_authentication_even_with_permission_callback_disabled(): void {
               register_rest_route(
                       'artpulse/v1',
                       '/widget-settings/(?P<id>[a-z0-9_-]+)',
                       array(
                               array(
                                       'methods'            => 'POST',
                                       'callback'           => array( WidgetSettingsRestController::class, 'save_settings' ),
                                       'permission_callback' => '__return_true',
                               ),
                       ),
                       true
               );

               wp_set_current_user( 0 );
               $req = new \WP_REST_Request( 'POST', '/artpulse/v1/widget-settings/test-widget' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $req->set_body_params( array( 'settings' => array( 'limit' => 5 ) ) );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 403, $res->get_status() ); // 403: unauthenticated requests are forbidden
       }
}
