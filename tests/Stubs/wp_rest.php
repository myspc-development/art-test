<?php
namespace {
	if ( ! function_exists( 'rest_get_server' ) ) {
		function rest_get_server() {
			if ( isset( $GLOBALS['rest_server'] ) ) {
				$server = $GLOBALS['rest_server'];
			} else {
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
						public function get_routes() {
										return array();
						}
					};
				}
			}
			if ( ! method_exists( $server, 'dispatch' ) || ! method_exists( $server, 'get_routes' ) ) {
					$server = new class( $server ) {
							private $server;
						public function __construct( $server ) {
								$this->server = $server;
						}
						public function dispatch( $request ) {
							if ( method_exists( $this->server, 'dispatch' ) ) {
									return $this->server->dispatch( $request );
							}
								return (object) array(
									'status' => 200,
									'data'   => array(),
								);
						}
						public function get_routes() {
							if ( method_exists( $this->server, 'get_routes' ) ) {
									return $this->server->get_routes();
							}
								return array();
						}
						public function __call( $name, $arguments ) {
								return $this->server->$name( ...$arguments );
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
