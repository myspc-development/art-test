<?php
use ArtPulse\Widgets\SampleHelloWidget;

class SampleHelloWidgetTest extends \WP_UnitTestCase {
    public function test_render_output() {
        $widget = new SampleHelloWidget();
        $output = $widget->render();
        $this->assertNotEmpty($output, 'Render output should not be empty.');
    }
}
