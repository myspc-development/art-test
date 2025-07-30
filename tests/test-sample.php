<?php
class SampleTest extends WP_UnitTestCase {
    public function test_plugin_loaded() {
        $this->assertTrue( defined( 'ARTPULSE_PLUGIN_FILE' ) );
    }
}
