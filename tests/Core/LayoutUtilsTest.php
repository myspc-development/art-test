<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\LayoutUtils;
class LayoutUtilsTest extends TestCase {

	public function test_normalize_layout_filters_invalid_and_duplicates(): void {
		$layout   = array(
			array( 'id' => 'alpha' ),
			array(
				'id'      => 'beta',
				'visible' => false,
			),
			array( 'id' => 'alpha' ),
			'gamma',
		);
		$valid    = array( 'alpha', 'beta' );
		$expected = array(
			array(
				'id'      => 'alpha',
				'visible' => true,
			),
			array(
				'id'      => 'beta',
				'visible' => false,
			),
		);
		$logs     = array();
		$this->assertSame( $expected, LayoutUtils::normalize_layout( $layout, $valid, $logs ) );
		$this->assertSame( array( 'gamma' ), $logs );
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
