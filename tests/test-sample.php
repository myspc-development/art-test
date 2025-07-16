<?php
class SampleTest extends WP_UnitTestCase {
    public function test_plugin_activation() {
        $this->assertTrue(function_exists('artpulse_activate'));
    }
}
