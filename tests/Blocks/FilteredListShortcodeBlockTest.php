<?php
namespace ArtPulse\Blocks;

if ( ! function_exists( __NAMESPACE__ . '\do_shortcode' ) ) {
	function do_shortcode( $code ) {
		return 'SC:' . $code;
	}
}

namespace ArtPulse\Blocks\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Blocks\FilteredListShortcodeBlock;

/**

 * @group BLOCKS
 */

class FilteredListShortcodeBlockTest extends TestCase {

	public function test_render_callback_builds_shortcode(): void {
		$html     = FilteredListShortcodeBlock::render_callback(
			array(
				'postType'     => 'artpulse_artist',
				'taxonomy'     => 'artist_specialty',
				'terms'        => 'painting,sculpture',
				'postsPerPage' => 10,
			)
		);
		$expected = 'SC:[ap_filtered_list post_type="artpulse_artist" taxonomy="artist_specialty" terms="painting,sculpture" posts_per_page="10"]';
		$this->assertSame( $expected, $html );
	}
}
