<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventListingShortcode;

/**

 * @group FRONTEND
 */

class EventListingShortcodeTest extends WP_UnitTestCase {

	protected function setUp(): void {
			parent::setUp();

			register_taxonomy(
				'event_category',
				'artpulse_event',
				array(
					'label'  => 'Event Categories',
					'public' => true,
				)
			);
	}

	public function test_category_dropdown_rendered(): void {
			$captured_taxonomies = null;
			add_filter(
				'get_terms_args',
				function ( $args, $taxonomies ) use ( &$captured_taxonomies ) {
						$captured_taxonomies = $taxonomies;
						return $args;
				},
				10,
				2
			);

			$term = wp_insert_term( 'Music', 'event_category' );

			$this->setOutputCallback( static fn() => '' );
			ob_start();
			$html   = EventListingShortcode::render( array() );
			$output = ob_get_clean();

			$slug = get_term( $term['term_id'] )->slug;

			$this->assertSame( '', $output, 'Unexpected output buffer' );
			$this->assertContains( 'event_category', (array) $captured_taxonomies );
			$this->assertStringContainsString( '<select name="category"', $html );
			$this->assertStringContainsString( 'value="' . esc_attr( $slug ) . '"', $html );
	}
}
