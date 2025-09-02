<?php
namespace ArtPulse\Blocks\Tests;

use ArtPulse\Blocks\AdvancedTaxonomyFilterBlock;
use WP_Block_Type_Registry;

/**
 * @group BLOCKS
 */
class AdvancedTaxonomyFilterBlockTest extends \WP_UnitTestCase {

    public function test_render_callback_outputs_placeholder(): void {
        $html = AdvancedTaxonomyFilterBlock::render_callback( array() );
        $this->assertStringContainsString( 'artpulse-advanced-taxonomy-filter-block', $html );
        $this->assertStringContainsString( 'ap-spinner', $html );
    }

    public function test_register_block_is_idempotent(): void {
        $triggered = false;
        $listener  = function( $function, $message ) use ( &$triggered ) {
            if ( 'register_block_type' === $function && str_contains( $message, 'artpulse/advanced-taxonomy-filter' ) ) {
                $triggered = true;
            }
        };

        \add_action( 'doing_it_wrong_run', $listener, 10, 2 );

        AdvancedTaxonomyFilterBlock::register_block();
        AdvancedTaxonomyFilterBlock::register_block();

        \remove_action( 'doing_it_wrong_run', $listener, 10 );
        WP_Block_Type_Registry::get_instance()->unregister( 'artpulse/advanced-taxonomy-filter' );

        $this->assertFalse( $triggered, 'Incorrect-usage notice triggered.' );
    }
}
