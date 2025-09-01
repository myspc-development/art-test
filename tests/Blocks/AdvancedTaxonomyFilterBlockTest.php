<?php
namespace ArtPulse\Blocks\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Blocks\AdvancedTaxonomyFilterBlock;

/**

 * @group blocks

 */

class AdvancedTaxonomyFilterBlockTest extends TestCase {

	public function test_render_callback_outputs_placeholder(): void {
		$html = AdvancedTaxonomyFilterBlock::render_callback( array() );
		$this->assertStringContainsString( 'artpulse-advanced-taxonomy-filter-block', $html );
		$this->assertStringContainsString( 'ap-spinner', $html );
	}
}
