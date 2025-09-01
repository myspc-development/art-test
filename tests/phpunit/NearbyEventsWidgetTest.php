<?php
use ArtPulse\Widgets\NearbyEventsWidget;
use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'do_shortcode' ) ) {
	function do_shortcode( string $content ): string {
		return str_replace( '[near_me_events]', 'nearby events list', $content );
	}
}

/**

 * @group phpunit

 */

class NearbyEventsWidgetTest extends TestCase {
	public function test_render_outputs_shortcode(): void {
		$output = NearbyEventsWidget::render();

		$this->assertStringContainsString(
			'nearby events list',
			$output,
			'Widget should render the near_me_events shortcode.'
		);
	}
}
