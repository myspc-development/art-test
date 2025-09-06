<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\LayoutUtils;
/**
 * @group CORE
 */
class LayoutUtilsTest extends TestCase {

	public function test_normalize_layout_filters_invalid_and_duplicates(): void {
			$layout   = array(
				array( 'id' => 'widget_alpha' ),
				array(
					'id'      => 'widget_beta',
					'visible' => false,
				),
				array( 'id' => 'widget_alpha' ),
				'widget_gamma',
			);
			$valid    = array( 'widget_alpha', 'widget_beta' );
			$expected = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'widget_beta',
					'visible' => false,
				),
			);
			$logs     = array();
			$this->assertSame( $expected, LayoutUtils::normalize_layout( $layout, $valid, $logs ) );
			$this->assertSame( array( 'widget_gamma' ), $logs );
	}

	public function test_normalize_layout_keeps_original_slug_for_unknown_widget(): void {
			$layout = array(
				array( 'id' => 'foo-bar' ),
			);
			$valid  = array();
			$logs   = array();
			$result = LayoutUtils::normalize_layout( $layout, $valid, $logs );
			$this->assertSame( 'foo_bar', $result[0]['id'] );
			$this->assertSame( array( 'foo_bar' ), $logs );
	}

	public function test_merge_styles_sanitizes_keys_and_values(): void {
		$base    = array( 'background_color' => '#fff' );
		$updates = array(
			'background_color' => '#000',
			'padding'          => 'M',
		);
		$merged  = LayoutUtils::merge_styles( $base, $updates );
		$this->assertSame(
			array(
				'background_color' => '#000',
				'padding'          => 'M',
			),
			$merged
		);
	}
}
