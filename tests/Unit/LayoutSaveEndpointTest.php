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
        function wp_send_json_error( $data = null, $status = null ) {
                global $wp_send_json_error_status;
                $wp_send_json_error_status = $status;
                $response = json_encode( array( 'success' => false, 'data' => $data ) );
                wp_die( $response );
        }
}

if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
        function wp_die( $message = '' ) {
                throw new \Exception( $message );
        }
}

namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\LayoutSaveEndpoint;

/**

 * @group unit

 */

class LayoutSaveEndpointTest extends TestCase {

        public function test_sets_403_status_when_not_logged_in(): void {
                global $wp_send_json_error_status;
                $wp_send_json_error_status = 200;
                try {
                        LayoutSaveEndpoint::handle();
                        $this->fail( 'Expected wp_die to be called' );
                } catch ( \Exception $e ) {
                        $this->assertSame( '{"success":false,"data":null}', $e->getMessage() );
                }

                $this->assertSame( 403, $wp_send_json_error_status );
        }
}
