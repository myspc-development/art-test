<?php
use ArtPulse\Widgets\MyUpcomingEventsWidget;

class MyUpcomingEventsWidgetTest extends \WP_UnitTestCase {
    public function test_render_output() {
        $widget = new MyUpcomingEventsWidget();
        $output = $widget->render();
        $this->assertNotEmpty($output, 'Render output should not be empty.');
    }
}
