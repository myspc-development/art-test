<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Taxonomies\TaxonomiesRegistrar;

/**

 * @group FRONTEND

 */

class EventCardTaxonomyTest extends WP_UnitTestCase {

       private int $event_id;
       private bool $removed_do_blocks = false;

       public function set_up() {
               parent::set_up();

               if ( has_filter( 'the_content', 'do_blocks' ) ) {
                       remove_filter( 'the_content', 'do_blocks', 9 );
                       $this->removed_do_blocks = true;
               }

               TaxonomiesRegistrar::register_event_types();
               TaxonomiesRegistrar::insert_default_event_types();

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

       public function tear_down() {
               if ( $this->removed_do_blocks ) {
                       add_filter( 'the_content', 'do_blocks', 9 );
               }

               unregister_taxonomy( 'event_type' );
               parent::tear_down();
       }

        public function test_event_card_outputs_meta(): void {
                $html = ap_get_event_card( $this->event_id );
                $this->assertStringContainsString( 'Exhibition', $html );
                $this->assertStringContainsString( '&#64;', $html );
                $this->assertStringNotContainsString( 'org@example.com', $html );
        }

        public function test_single_template_outputs_meta(): void {
                $this->go_to( get_permalink( $this->event_id ) );
                $path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/salient/content-artpulse_event.php';
                $html = get_echo(
                        static function () use ( $path ) {
                                include $path;
                        }
                );
                $this->assertStringContainsString( 'Exhibition', $html );
                $this->assertStringContainsString( '&#64;', $html );
                $this->assertStringNotContainsString( 'org@example.com', $html );
        }
}
