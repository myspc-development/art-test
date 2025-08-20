<?php
use WP_REST_Request;

class PortfolioPermissionsTest extends WP_UnitTestCase {
    public function test_requires_authentication() {
        wp_set_current_user(0);
        $request = new WP_REST_Request('GET', '/artpulse/v1/portfolio/1');
        $response = rest_get_server()->dispatch($request);
        $status = $response->get_status();
        $this->assertTrue(in_array($status, [401,403], true));
    }
}
