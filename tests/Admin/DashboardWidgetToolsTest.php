<?php
use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;

/**

 * @group ADMIN
 */

class DashboardWidgetToolsTest extends TestCase {

	public function test_get_role_widgets_returns_array() {
		$this->assertIsArray( DashboardWidgetTools::get_role_widgets() );
	}
}
