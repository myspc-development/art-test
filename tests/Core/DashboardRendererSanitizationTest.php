<?php
namespace ArtPulse\Core\Tests;

use ArtPulse\Core\DashboardRenderer;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group core

 */

class DashboardRendererSanitizationTest extends \WP_UnitTestCase {
	public function test_filtered_output_is_sanitized() {
		DashboardWidgetRegistry::register_widget(
			'widget_sanitize_test',
			array(
				'label'    => 'Sanitize Test',
				'callback' => '__return_empty_string',
			)
		);

		$unsafe = '<img src="x" onerror="alert(1)" />';
		add_filter(
			'ap_dashboard_rendered_widget',
			function ( $output, $widget_id, $user_id ) use ( $unsafe ) {
				return $unsafe;
			},
			10,
			3
		);

		$renderer = new DashboardRenderer();
		$output   = $renderer->renderWidget(
			'widget_sanitize_test',
			array(
				'gate_caps'  => false,
				'gate_flags' => false,
			),
			0
		);

		remove_all_filters( 'ap_dashboard_rendered_widget' );

		$this->assertSame( wp_kses_post( $unsafe ), $output );
	}
}
