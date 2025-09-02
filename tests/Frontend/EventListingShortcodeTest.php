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
               $term  = wp_insert_term( 'Music', 'event_category' );
               $html  = EventListingShortcode::render( array() );
               $this->assertStringContainsString( '<select name="category"', $html );
               $slug = get_term( $term['term_id'] )->slug;
               $this->assertStringContainsString( 'value="' . esc_attr( $slug ) . '"', $html );
       }
}
