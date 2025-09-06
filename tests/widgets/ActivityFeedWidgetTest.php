<?php

use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\Member\ActivityFeedWidget;

/**

 * @group WIDGETS
 */

class ActivityFeedWidgetTest extends WidgetTestCase {
	public function test_render_output() {
		$widget = new ActivityFeedWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
