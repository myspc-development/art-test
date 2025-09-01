<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Taxonomies\TaxonomiesRegistrar;

/**

 * @group frontend

 */

class EventCardTaxonomyTest extends WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		TaxonomiesRegistrar::register();
		do_action( 'init' );

		$term           = get_term_by( 'slug', 'exhibition', 'event_type' );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Tax Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		if ( $term ) {
			wp_set_post_terms( $this->event_id, array( $term->term_id ), 'event_type' );
		}
		update_post_meta( $this->event_id, 'event_organizer_name', 'Organizer' );
		update_post_meta( $this->event_id, 'event_organizer_email', 'org@example.com' );
	}

	public function test_event_card_outputs_meta(): void {
		$html = ap_get_event_card( $this->event_id );
		$this->assertStringContainsString( 'Exhibition', $html );
		$this->assertStringContainsString( 'org@example.com', $html );
	}

	public function test_single_template_outputs_meta(): void {
		$this->go_to( get_permalink( $this->event_id ) );
		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/salient/content-artpulse_event.php';
		$html = $this->get_echo(
			static function () use ( $path ) {
				include $path;
			}
		);
		$this->assertStringContainsString( 'Exhibition', $html );
		$this->assertStringContainsString( 'org@example.com', $html );
	}
}
