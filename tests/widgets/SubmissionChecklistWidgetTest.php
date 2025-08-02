<?php
use ArtPulse\Widgets\SubmissionChecklistWidget;

class SubmissionChecklistWidgetTest extends \WP_UnitTestCase {
    public function test_render_output() {
        $widget = new SubmissionChecklistWidget();
        $output = $widget->render();
        $this->assertNotEmpty($output, 'Render output should not be empty.');
    }
}
