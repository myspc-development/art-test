<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Rest\DashboardConfigController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class DashboardConfigEndpointTest extends \WP_UnitTestCase {
        private int $admin_id;
        private int $user_id;

        public function set_up() {
                parent::set_up();
               $this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
               $this->user_id  = self::factory()->user->create( array( 'role' => 'subscriber' ) );

                DashboardWidgetRegistry::set( [] );

               DashboardWidgetRegistry::register( 'one', 'One', '', '', '__return_null', array(
                       'capability'    => 'edit_posts',
                       'exclude_roles' => array( 'subscriber' ),
               ) );
                DashboardWidgetRegistry::register( 'two', 'Two', '', '', '__return_null' );
                DashboardConfigController::register();
                do_action( 'rest_api_init' );
        }

        public function test_get_requires_read_and_returns_expected_structure(): void {
                wp_set_current_user( 0 );
                $req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-config' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 403, $res->get_status() );

                wp_set_current_user( $this->user_id );
                update_option( 'artpulse_widget_roles', array( 'subscriber' => array( 'one' ) ) );
                update_option( 'artpulse_dashboard_layouts', array( 'subscriber' => array( 'one', 'two' ) ) );
                update_option( 'artpulse_locked_widgets', array( 'two' ) );

                $req2 = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-config' );
                $res2 = rest_get_server()->dispatch( $req2 );
                $this->assertSame( 200, $res2->get_status() );
               $data = $res2->get_data();
               $this->assertSame( array( 'subscriber' => array( 'widget_one' ) ), $data['widget_roles'] );
               $this->assertSame( array( 'subscriber' => array( 'widget_one', 'widget_two' ) ), $data['role_widgets'] );
               $this->assertSame( array( 'widget_two' ), $data['locked'] );
               $this->assertSame( array( 'widget_one' => 'edit_posts' ), $data['capabilities'] );
               $this->assertSame( array( 'widget_one' => array( 'subscriber' ) ), $data['excluded_roles'] );
       }

       public function test_response_keys_are_canonical_and_unique(): void {
               // Replace default registry with duplicates using mixed slugs.
               DashboardWidgetRegistry::set( array() );
               DashboardWidgetRegistry::register(
                       'news',
                       'News',
                       '',
                       '',
                       '__return_null',
                       array(
                               'capability'    => 'edit_posts',
                               'exclude_roles' => array( 'subscriber' ),
                       )
               );
               DashboardWidgetRegistry::register(
                       'widget_news',
                       'News Alias',
                       '',
                       '',
                       '__return_null',
                       array(
                               'capability'    => 'delete_posts',
                               'exclude_roles' => array( 'administrator' ),
                       )
               );

               update_option(
                       'artpulse_widget_roles',
                       array( 'subscriber' => array( 'news', 'widget_news' ) )
               );
               update_option(
                       'artpulse_dashboard_layouts',
                       array( 'subscriber' => array( 'news', 'widget_news' ) )
               );
               update_option(
                       'artpulse_locked_widgets',
                       array( 'news', 'widget_news' )
               );

               wp_set_current_user( $this->user_id );
               $req  = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-config' );
               $res  = rest_get_server()->dispatch( $req );
               $this->assertSame( 200, $res->get_status() );
               $data = $res->get_data();
               $this->assertSame( array( 'subscriber' => array( 'widget_news' ) ), $data['widget_roles'] );
               $this->assertSame( array( 'subscriber' => array( 'widget_news' ) ), $data['role_widgets'] );
               $this->assertSame( array( 'widget_news' ), $data['locked'] );
               $this->assertSame( array( 'widget_news' => 'edit_posts' ), $data['capabilities'] );
               $this->assertSame( array( 'widget_news' => array( 'subscriber' ) ), $data['excluded_roles'] );

               foreach ( $data['widget_roles'] as $ids ) {
                       foreach ( $ids as $id ) {
                               $this->assertSame( 0, strpos( $id, 'widget_' ) );
                       }
               }
               foreach ( $data['role_widgets'] as $ids ) {
                       foreach ( $ids as $id ) {
                               $this->assertSame( 0, strpos( $id, 'widget_' ) );
                       }
               }
               foreach ( $data['locked'] as $id ) {
                       $this->assertSame( 0, strpos( $id, 'widget_' ) );
               }
               foreach ( $data['layout'] as $items ) {
                       foreach ( $items as $item ) {
                               $this->assertSame( 0, strpos( $item['id'], 'widget_' ) );
                       }
               }
               foreach ( array_keys( $data['capabilities'] ) as $id ) {
                       $this->assertSame( 0, strpos( $id, 'widget_' ) );
               }
               foreach ( array_keys( $data['excluded_roles'] ) as $id ) {
                       $this->assertSame( 0, strpos( $id, 'widget_' ) );
               }
       }

       public function test_post_enforces_permissions_and_nonce(): void {
               // Subscribers should be blocked even with a valid nonce.
               wp_set_current_user( $this->user_id );
               $req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
               $req->set_body_params( array() );
               $req->set_header( 'Content-Type', 'application/json' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $req->set_header( 'X-AP-Nonce', wp_create_nonce( 'ap_dashboard_config' ) );
               $req->set_body( json_encode( array( 'widget_roles' => array( 'subscriber' => array( 'one' ) ) ) ) );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 403, $res->get_status() );
               $this->assertSame( 'rest_forbidden', $res->get_data()['code'] );

               // Admins without a nonce should be rejected.
               wp_set_current_user( $this->admin_id );
               $missing = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
               $missing->set_body_params( array() );
               $missing->set_header( 'Content-Type', 'application/json' );
               $missing->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $missing->set_body( json_encode( array( 'widget_roles' => array( 'administrator' => array( 'one' ) ) ) ) );
               $res_missing = rest_get_server()->dispatch( $missing );
               $this->assertSame( 401, $res_missing->get_status() );

               // Admins with a valid nonce should succeed.
               $good = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
               $good->set_body_params( array() );
               $good->set_header( 'Content-Type', 'application/json' );
               $good->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $good->set_header( 'X-AP-Nonce', wp_create_nonce( 'ap_dashboard_config' ) );
               $good->set_body( json_encode( array(
                       'widget_roles' => array( 'administrator' => array( 'one' ) ),
                       'role_widgets' => array( 'administrator' => array( 'one', 'two' ) ),
                       'locked'       => array( 'two' ),
               ) ) );
               $res_good = rest_get_server()->dispatch( $good );
               $this->assertSame( 200, $res_good->get_status() );
               $this->assertSame(
                       array( 'administrator' => array( 'widget_one' ) ),
                       get_option( 'artpulse_widget_roles' )
               );
               $this->assertSame(
                       array( 'administrator' => array( 'widget_one', 'widget_two' ) ),
                       get_option( 'artpulse_dashboard_layouts' )
               );
               $this->assertSame( array( 'widget_two' ), get_option( 'artpulse_locked_widgets' ) );
       }
}

