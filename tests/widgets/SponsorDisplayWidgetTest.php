<?php
use ArtPulse\Widgets\SponsorDisplayWidget;

if ( ! function_exists( 'is_singular' ) ) {
	function is_singular(): bool {
		return false;
	}
}
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ): void {}
}

/**

 * @group WIDGETS

 */

class SponsorDisplayWidgetTest extends \WP_UnitTestCase {
	public function test_append_disclosure_returns_string() {
		$result = SponsorDisplayWidget::append_disclosure( 'content' );
		$this->assertIsString( $result );
		$this->assertSame( 'content', $result );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_append_disclosure_returns_string_in_preview() {
		class_exists( SponsorDisplayWidget::class );
		define( 'IS_DASHBOARD_BUILDER_PREVIEW', true );
		$result = SponsorDisplayWidget::append_disclosure( 'content' );
		$this->assertIsString( $result );
		$this->assertSame( 'content', $result );
	}
}
