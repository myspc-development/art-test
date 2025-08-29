<?php
namespace ArtPulse\Rest;

if ( ! function_exists( __NAMESPACE__ . '\\is_user_logged_in' ) ) {
        function is_user_logged_in() {
                return false;
        }
}

if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
        function current_user_can( $cap ) {
                return false;
        }
}

if ( ! function_exists( __NAMESPACE__ . '\\wp_send_json_error' ) ) {
        function wp_send_json_error( $data, $status = null ) {
                global $wp_send_json_error_status;
                $wp_send_json_error_status = $status;
        }
}

if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
        function wp_die() {
                throw new \Exception( 'wp_die called' );
        }
}

namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\LayoutSaveEndpoint;

class LayoutSaveEndpointTest extends TestCase {

        public function test_sets_403_status_when_not_logged_in(): void {
                global $wp_send_json_error_status;
                $wp_send_json_error_status = 200;
                try {
                        LayoutSaveEndpoint::handle();
                        $this->fail( 'Expected wp_die to be called' );
                } catch ( \Exception $e ) {
                        $this->assertSame( 'wp_die called', $e->getMessage() );
                }

                $this->assertSame( 403, $wp_send_json_error_status );
        }
}
