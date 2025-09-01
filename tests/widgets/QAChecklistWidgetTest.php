<?php
require_once __DIR__ . '/bootstrap.php';

use ArtPulse\Tests\Widgets\WidgetTestCase;
use ArtPulse\Widgets\QAChecklistWidget;

/**

 * @group widgets

 */

class QAChecklistWidgetTest extends WidgetTestCase {
	public function test_render_output() {
		$widget = new QAChecklistWidget();
		$output = $widget->render();
		$this->assertNotEmpty( $output, 'Render output should not be empty.' );
	}
}
