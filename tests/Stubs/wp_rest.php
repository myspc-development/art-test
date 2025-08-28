<?php
namespace {
	if ( ! function_exists( 'rest_get_server' ) ) {
		function rest_get_server() {
			static $server;
			if ( ! $server ) {
				$server = new class() {
					public function dispatch( $request ) {
						// Minimal fake response
						return (object) array(
							'status' => 200,
							'data'   => array(),
						);
					}
				};
			}
			return $server;
		}
	}
	if ( ! function_exists( 'rest_do_request' ) ) {
		function rest_do_request( $routeOrRequest ) {
			return rest_get_server()->dispatch( $routeOrRequest );
		}
	}
}
