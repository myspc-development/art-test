<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\AnalyticsManager;

/**

 * @group CORE

 */

class AnalyticsManagerTest extends WP_UnitTestCase {

	public function test_print_tracking_snippet_outputs_ga4_when_enabled(): void {
		update_option(
			'artpulse_settings',
			array(
				'analytics_enabled' => 1,
				'analytics_gtag_id' => 'G-TEST',
			)
		);

		ob_start();
		AnalyticsManager::printTrackingSnippet();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'ArtPulse GA4', $output );
		$this->assertStringContainsString( 'gtag/js?id=G-TEST', $output );
	}

	public function test_print_tracking_snippet_no_output_when_disabled(): void {
		update_option(
			'artpulse_settings',
			array(
				'analytics_enabled' => 0,
				'analytics_gtag_id' => 'G-TEST',
			)
		);

		ob_start();
		AnalyticsManager::printTrackingSnippet();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}
}
