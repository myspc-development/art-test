<?php
namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\EventFilter;
use ArtPulse\Frontend\LoginShortcode;
use ArtPulse\Frontend\OrganizationDashboardShortcode;
use ArtPulse\Core\Plugin;
use WP_UnitTestCase;

class AjaxHandlerNonceTest extends WP_UnitTestCase {
    private array $patchHandles = array();

    public function tear_down(): void {
        $_POST = array();
        $_REQUEST = array();
        foreach ( $this->patchHandles as $h ) {
            \Patchwork\restore( $h );
        }
        $this->patchHandles = array();
        parent::tear_down();
    }

    private function replace_login_stubs(): void {
        if ( function_exists( '\\ArtPulse\\Frontend\\check_ajax_referer' ) ) {
            $this->patchHandles[] = \Patchwork\redefine(
                'ArtPulse\\Frontend\\check_ajax_referer',
                function( $action, $name = false, $die = true ) {
                    return \check_ajax_referer( $action, $name, $die );
                }
            );
        }
        if ( function_exists( '\\ArtPulse\\Frontend\\wp_signon' ) ) {
            $this->patchHandles[] = \Patchwork\redefine(
                'ArtPulse\\Frontend\\wp_signon',
                function( $creds, $secure = false ) {
                    return \wp_signon( $creds, $secure );
                }
            );
        }
        if ( function_exists( '\\ArtPulse\\Frontend\\wp_send_json_success' ) ) {
            $this->patchHandles[] = \Patchwork\redefine(
                'ArtPulse\\Frontend\\wp_send_json_success',
                function( $data ) {
                    \wp_send_json_success( $data );
                }
            );
        }
        if ( function_exists( '\\ArtPulse\\Frontend\\wp_send_json_error' ) ) {
            $this->patchHandles[] = \Patchwork\redefine(
                'ArtPulse\\Frontend\\wp_send_json_error',
                function( $data ) {
                    \wp_send_json_error( $data );
                }
            );
        }
    }

    public function test_event_filter_handler_outputs_html_with_valid_nonce(): void {
        EventFilter::register();
        $event_id = self::factory()->post->create(
            array(
                'post_type'   => 'artpulse_event',
                'post_status' => 'publish',
                'post_title'  => 'Sample Event',
            )
        );
        $_REQUEST['nonce'] = wp_create_nonce( 'ap_event_filter_nonce' );

        ob_start();
        try {
            do_action( 'wp_ajax_ap_filter_events' );
        } catch ( \WPDieException $e ) {
            // expected termination
        }
        $html = ob_get_clean();

        $this->assertStringContainsString( 'Sample Event', $html );
    }

    public function test_event_filter_handler_fails_with_invalid_nonce(): void {
        EventFilter::register();
        $_REQUEST['nonce'] = 'bad';

        try {
            do_action( 'wp_ajax_ap_filter_events' );
            $this->fail( 'Expected WPDieException' );
        } catch ( \WPDieException $e ) {
            $this->assertSame( '-1', $e->getMessage() );
        }
    }

    public function test_login_handler_outputs_json_with_valid_nonce(): void {
        LoginShortcode::register();
        $this->replace_login_stubs();

        $user_id = self::factory()->user->create( array( 'user_login' => 'jdoe', 'user_pass' => 'pass' ) );
        $_POST['nonce']    = wp_create_nonce( 'ap_login_nonce' );
        $_POST['username'] = 'jdoe';
        $_POST['password'] = 'pass';

        try {
            do_action( 'wp_ajax_nopriv_ap_do_login' );
        } catch ( \WPDieException $e ) {
            $json = $e->getMessage();
        }

        $data = json_decode( $json, true );
        $this->assertTrue( $data['success'] );
        $this->assertSame( 'Signed in successfully.', $data['data']['message'] );
        $this->assertSame( Plugin::get_user_dashboard_url(), $data['data']['dashboardUrl'] );
    }

    public function test_login_handler_fails_with_invalid_nonce(): void {
        LoginShortcode::register();
        $this->replace_login_stubs();

        $_POST['nonce']    = 'bad';
        $_POST['username'] = 'jdoe';
        $_POST['password'] = 'pass';

        try {
            do_action( 'wp_ajax_nopriv_ap_do_login' );
            $this->fail( 'Expected WPDieException' );
        } catch ( \WPDieException $e ) {
            $this->assertSame( '-1', $e->getMessage() );
        }
    }

    public function test_org_dashboard_get_event_outputs_json_with_valid_nonce(): void {
        OrganizationDashboardShortcode::register();
        $user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $event_id = self::factory()->post->create(
            array(
                'post_type'   => 'artpulse_event',
                'post_status' => 'publish',
                'post_title'  => 'Org Event',
            )
        );
        $_POST['nonce']    = wp_create_nonce( 'ap_org_dashboard_nonce' );
        $_POST['event_id'] = $event_id;

        try {
            do_action( 'wp_ajax_ap_get_org_event' );
        } catch ( \WPDieException $e ) {
            $json = $e->getMessage();
        }

        $data = json_decode( $json, true );
        $this->assertTrue( $data['success'] );
        $this->assertSame( 'Org Event', $data['data']['ap_event_title'] );
    }

    public function test_org_dashboard_get_event_fails_with_invalid_nonce(): void {
        OrganizationDashboardShortcode::register();
        $user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $event_id = self::factory()->post->create(
            array(
                'post_type'   => 'artpulse_event',
                'post_status' => 'publish',
            )
        );
        $_POST['nonce']    = 'bad';
        $_POST['event_id'] = $event_id;

        try {
            do_action( 'wp_ajax_ap_get_org_event' );
            $this->fail( 'Expected WPDieException' );
        } catch ( \WPDieException $e ) {
            $this->assertSame( '-1', $e->getMessage() );
        }
    }
}
