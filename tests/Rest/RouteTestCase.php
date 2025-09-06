<?php
class RouteTestCase extends WP_UnitTestCase {
	protected function req( string $method, string $path, array $params = array(), int $user = 0 ) {
		wp_set_current_user( $user );
		$request = new WP_REST_Request( strtoupper( $method ), $path );
		foreach ( $params as $k => $v ) {
			$request->set_param( $k, $v ); }
		return rest_do_request( $request );
	}
}
