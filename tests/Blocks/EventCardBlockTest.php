<?php
namespace ArtPulse\Blocks\Tests;

use ArtPulse\Blocks\EventCardBlock;
use WP_Block_Type_Registry;

/**
 * @group BLOCKS
 */
class EventCardBlockTest extends \WP_UnitTestCase {
        public function test_register_block_is_idempotent(): void {
                $triggered = false;
                $listener  = function ( $function, $message ) use ( &$triggered ) {
                        if ( in_array( $function, array( 'register_block_type', 'register_block_type_from_metadata' ), true ) && str_contains( $message, 'artpulse/event-card' ) ) {
                                $triggered = true;
                        }
                };

                \add_action( 'doing_it_wrong_run', $listener, 10, 2 );

                EventCardBlock::register_block();
                EventCardBlock::register_block();

                \remove_action( 'doing_it_wrong_run', $listener, 10 );
                WP_Block_Type_Registry::get_instance()->unregister( 'artpulse/event-card' );

                $this->assertFalse( $triggered, 'Incorrect-usage notice triggered.' );
        }
}
