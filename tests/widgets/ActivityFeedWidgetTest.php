<?php
use ArtPulse\Widgets\Member\ActivityFeedWidget;

class ActivityFeedWidgetTest extends \WP_UnitTestCase {
    public function test_render_output() {
        $widget = new ActivityFeedWidget();
        $output = $widget->render();
        $this->assertNotEmpty($output, 'Render output should not be empty.');
    }
}
