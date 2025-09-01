<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventListingShortcode;

/**

 * @group frontend

 */

class EventListingShortcodeTest extends WP_UnitTestCase {

	public function test_category_dropdown_rendered(): void {
		$cat_id = wp_create_category( 'Music' );
		$html   = EventListingShortcode::render( array() );
		$this->assertStringContainsString( '<select name="category"', $html );
		$slug = get_term( $cat_id )->slug;
		$this->assertStringContainsString( 'value="' . esc_attr( $slug ) . '"', $html );
	}
}
