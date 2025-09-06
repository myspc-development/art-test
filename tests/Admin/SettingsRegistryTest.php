<?php
use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\SettingsRegistry;

/**

 * @group ADMIN
 */

class SettingsRegistryTest extends TestCase {

	public function test_register_tab() {
		SettingsRegistry::register_tab( 'widgets', 'Dashboard Widgets' );
		$tabs = SettingsRegistry::get_tabs();
		$this->assertArrayHasKey( 'widgets', $tabs );
	}
}
