<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DashboardPreviewController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group REST
 */
class DashboardPreviewControllerTest extends \WP_UnitTestCase {

        private int $admin_id;

        public function set_up() {
                parent::set_up();
                $this->admin_id = self::factory()->user->create(
                        array(
                                'role'         => 'administrator',
                                'display_name' => 'Tester',
                        )
                );

                // Register a sample widget so the widgets list is populated.
                DashboardWidgetRegistry::register(
                        'widget_sample',
                        array(
                                'title'           => 'Sample',
                                'render_callback' => '__return_null',
                                'roles'           => array( 'administrator' ),
                        )
                );

                DashboardPreviewController::register();
                do_action( 'rest_api_init' );
        }

        public function test_get_preview_requires_permission(): void {
                wp_set_current_user( 0 );
                $req = new \WP_REST_Request( 'GET', '/artpulse/v1/preview/dashboard' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
        }

        public function test_get_preview_returns_user_and_widgets(): void {
                wp_set_current_user( $this->admin_id );
                $req = new \WP_REST_Request( 'GET', '/artpulse/v1/preview/dashboard' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 200, $res->get_status() );
                $data = $res->get_data();
                $this->assertSame( 'Tester', $data['user'] );
                $this->assertIsArray( $data['widgets'] );
        }
}
