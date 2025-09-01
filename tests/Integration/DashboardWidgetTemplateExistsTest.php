<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group INTEGRATION

 */

class DashboardWidgetTemplateExistsTest extends \WP_UnitTestCase {
	public function test_registered_widget_templates_exist(): void {
		$map = json_decode( file_get_contents( __DIR__ . '/../../config/widgets-map.json' ), true );
		foreach ( $map as $slug => $meta ) {
			if ( empty( $meta['template'] ) ) {
				continue; // deprecated or unimplemented
			}
			$path = dirname( __DIR__, 2 ) . '/' . $meta['template'];
			$this->assertFileExists( $path, "Template for {$slug} missing" );
		}
	}
}
