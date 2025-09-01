<?php

use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\QAChecklistWidget;

/**

 * @group WIDGETS

 */

class QAChecklistWidgetTest extends WidgetTestCase {
	public function test_render_output() {
		$widget = new QAChecklistWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
