<?php
namespace ArtPulse\Widgets\Placeholder;

if ( ! function_exists( __NAMESPACE__ . '\esc_html' ) ) {
	function esc_html( $text ) {
		return $text; }
}
if ( ! function_exists( __NAMESPACE__ . '\apply_filters' ) ) {
	function apply_filters( $tag, $value, $args = null ) {
		return $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth ); }
}

namespace ArtPulse\Widgets\Placeholder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

/**

 * @group UNIT
 */

class ApPlaceholderWidgetTest extends TestCase {

	protected function setUp(): void {
		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}
	}

	public function test_debug_encoded_when_non_empty(): void {
		ob_start();
				ApPlaceholderWidget::render( null, array( 'debug' => array( 'widget_foo' => 'bar' ) ) );
		$html = ob_get_clean();
				$this->assertStringContainsString( json_encode( array( 'widget_foo' => 'bar' ) ), $html );
		$this->assertStringContainsString( 'ap-widget__debug', $html );
	}

	public function test_debug_not_shown_when_empty(): void {
		ob_start();
				ApPlaceholderWidget::render( null, array( 'debug' => '' ) );
		$html = ob_get_clean();
		$this->assertStringNotContainsString( 'ap-widget__debug', $html );
	}
}
