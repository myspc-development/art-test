<?php
use ArtPulse\Widgets\EventsWidget;

class EventsWidgetTest extends \WP_UnitTestCase {
	public function test_render_output() {
		$widget = new EventsWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
