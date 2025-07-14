<?php
namespace ArtPulse\Tests;

use WP_UnitTestCase;

class UpdaterTest extends WP_UnitTestCase {
    public function test_update_endpoint_returns_200() {
        do_action('rest_api_init');
        $response = rest_do_request('/artpulse/v1/update/diagnostics');
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertArrayHasKey('http_code', $data);
    }
}
