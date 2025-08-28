<?php
namespace ArtPulse\Integration\Tests;

use WP_Ajax_UnitTestCase;
use AjaxTestHelper;

class LayoutSaveEndpointAjaxTest extends WP_Ajax_UnitTestCase {

        use AjaxTestHelper;

        public function tear_down(): void {
                $this->reset_superglobals();
                parent::tear_down();
        }

        public function test_requires_login(): void {
                $this->set_nonce( 'ap_dashboard_nonce' );

                try {
                        $this->_handleAjax( 'save_dashboard_layout' );
                        $this->fail( 'Expected forbidden response' );
                } catch ( \WPAjaxDieStopException $e ) {
                        $this->assertSame( 403, http_response_code() );
                        $resp = json_decode( $this->_last_response, true );
                        $this->assertFalse( $resp['success'] );
                        $this->assertSame( 'Forbidden', $resp['data']['message'] );
                }
        }
}
