<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Response;

/**
 * Helper methods for consistent REST responses.
 */
trait RestResponder {
    /**
     * Create a success REST response.
     *
     * @param mixed $data   Response data.
     * @param int   $status HTTP status code.
     *
     * @return WP_REST_Response
     */
    protected function ok( $data = array(), int $status = 200 ): WP_REST_Response {
        return new WP_REST_Response( $data, $status );
    }

    /**
     * Create an error REST response.
     *
     * @param string $message Error message.
     * @param string $code    Error code.
     * @param int    $status  HTTP status code.
     * @param array  $data    Additional error data.
     *
     * @return WP_Error
     */
    protected function fail( string $message, string $code = 'rest_error', int $status = 400, array $data = array() ): WP_Error {
        return new WP_Error( $code, $message, array_merge( array( 'status' => $status ), $data ) );
    }
}
