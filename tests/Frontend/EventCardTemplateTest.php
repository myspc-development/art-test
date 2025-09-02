<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Taxonomies\TaxonomiesRegistrar;

/**

 * @group FRONTEND

 */

class EventCardTemplateTest extends WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		TaxonomiesRegistrar::register();
		do_action( 'init' );

		$term           = get_term_by( 'slug', 'exhibition', 'event_type' );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Card Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		if ( $term ) {
			wp_set_post_terms( $this->event_id, array( $term->term_id ), 'event_type' );
		}
	}

        public function test_event_type_rendered_in_card(): void {
                $html = ap_get_event_card( $this->event_id );
                $this->assertStringContainsString( 'Exhibition', $html );
        }

        public function test_organizer_email_not_encoded(): void {
                update_post_meta( $this->event_id, 'event_organizer_email', 'organizer@example.com' );
                $html = ap_get_event_card( $this->event_id );
                $this->assertStringContainsString( 'organizer@example.com', $html );
                $this->assertStringNotContainsString( '&#', $html );
        }
}
