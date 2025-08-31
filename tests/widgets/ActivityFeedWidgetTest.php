<?php
require_once __DIR__ . '/bootstrap.php';

use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\Member\ActivityFeedWidget;

class ActivityFeedWidgetTest extends WidgetTestCase {
	public function test_render_output() {
		$widget = new ActivityFeedWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
