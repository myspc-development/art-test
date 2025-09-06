<?php
namespace ArtPulse\Blocks\Tests;

use ArtPulse\Blocks\RelatedProjectsBlock;
use WP_Block_Type_Registry;

/**
 * @group BLOCKS
 */
class RelatedProjectsBlockTest extends \WP_UnitTestCase {
	protected function tearDown(): void {
		WP_Block_Type_Registry::get_instance()->unregister( 'artpulse/related-projects' );
		parent::tearDown();
	}

	public function test_register_block_is_idempotent(): void {
		$triggered = false;
		$listener  = function ( $function, $message ) use ( &$triggered ) {
			if ( in_array( $function, array( 'register_block_type', 'register_block_type_from_metadata' ), true ) &&
				str_contains( $message, 'artpulse/related-projects' ) ) {
				$triggered = true;
			}
		};

		\add_action( 'doing_it_wrong_run', $listener, 10, 2 );

		RelatedProjectsBlock::register_block();
		RelatedProjectsBlock::register_block();

		\remove_action( 'doing_it_wrong_run', $listener, 10 );
		$this->assertFalse( $triggered, 'Incorrect-usage notice triggered.' );
	}
}
