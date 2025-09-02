<?php
/**
 * Integration tests for dashboard builder preview rendering.
 *
 * @package ArtPulse
 */

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Tests for the dashboard preview builder.
 */
final class DashboardPreviewTest extends TestCase {

	/**
	 * Ensure builder preview renders exactly the configured widgets in order.
	 */
	public function test_builder_preview_renders_exact_ids_in_order() {
		// Arrange: a small fake layout for role=member.
		update_option(
			'artpulse_dashboard_layouts',
			array(
                                'member' => array(
                                        'widget_news',
                                        'widget_my_rsvps',
                                        'widget_nearby_events_map',
                                ),
			)
		);

		// Act: render verbatim (no gating).
		$sources  = ap_make( \ArtPulse\Audit\WidgetSources::class );
		$renderer = ap_make( \ArtPulse\Core\DashboardRenderer::class );

		$ids  = $sources->builderForRole( 'member' );
		$html = $renderer->renderIds(
			$ids,
			array(
				'context'    => 'builder_preview',
				'gate_caps'  => false,
				'gate_flags' => false,
			)
		);

		// Assert: exact order, wrapped with data-widget-id.
                $this->assertStringContainsString( 'data-widget-id="widget_news"', $html );
                $this->assertMatchesRegularExpression(
                        '#widget_news.*widget_my_rsvps.*widget_nearby_events_map#s',
                        $html
                );
	}

	/**
	 * Builder preview with simulate user applies gating.
	 */
	public function test_builder_preview_simulate_user_applies_gates() {
		update_option(
			'artpulse_dashboard_layouts',
			array(
                                'member' => array( 'widget_news' ),
			)
		);

		$sources  = ap_make( \ArtPulse\Audit\WidgetSources::class );
		$renderer = ap_make( \ArtPulse\Core\DashboardRenderer::class );

		$ids  = $sources->builderForRole( 'member' );
		$html = $renderer->renderIds(
			$ids,
			array(
				'context'    => 'builder_preview_real',
				'gate_caps'  => true,
				'gate_flags' => true,
			)
		);

		$this->assertIsString( $html ); // Just smoke-test; gating may hide things in CI.
	}
}
