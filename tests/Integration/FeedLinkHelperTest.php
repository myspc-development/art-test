<?php
namespace ArtPulse\Integration\Tests;

use PHPUnit\Framework\TestCase;
use function ArtPulse\Frontend\ap_filtered_feed_link;

/**

 * @group INTEGRATION

 */

class FeedLinkHelperTest extends TestCase {

	public function test_builds_filtered_url(): void {
		$url = ap_filtered_feed_link(
			array(
				'org' => 12,
				'tag' => 'print',
			)
		);
		$this->assertStringContainsString( 'feeds/events.ics', $url );
		$this->assertStringContainsString( 'org=12', $url );
		$this->assertStringContainsString( 'tag=print', $url );
	}
}
