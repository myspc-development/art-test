<?php
use ArtPulse\Widgets\QAChecklistWidget;

class QAChecklistWidgetTest extends \WP_UnitTestCase {
	public function test_render_output() {
		$widget = new QAChecklistWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
