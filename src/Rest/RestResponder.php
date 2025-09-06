<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Response;

trait RestResponder {
	protected function ok( array|object $data, int $status = 200 ): WP_REST_Response {
		$r = rest_ensure_response( $data );
		$r->set_status( $status );
		return $r;
	}

	protected function fail( string $code, string $message, int $status = 400, array $data = array() ): WP_Error {
		return new WP_Error( $code, $message, array( 'status' => $status ) + $data );
	}
}
