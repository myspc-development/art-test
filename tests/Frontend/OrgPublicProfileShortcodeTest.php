<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrgPublicProfileShortcode;

/**

 * @group FRONTEND
 */

class OrgPublicProfileShortcodeTest extends TestCase {

	protected function setUp(): void {
			\ArtPulse\Frontend\StubState::reset();
			\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_profile_published', '1' );
			\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_tagline', 'Best Org' );
			\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_theme_color', '#abc' );
			\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_logo_id', 4 );
			\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_banner_id', 5 );
			\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_description', 'About us' );
			\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_featured_events', '2,3' );
			\ArtPulse\Frontend\StubState::$page = null;
	}

	public function test_render_outputs_tagline(): void {
			$this->setOutputCallback( static fn() => '' );
			ob_start();
			$html   = OrgPublicProfileShortcode::render( array( 'id' => 1 ) );
			$output = ob_get_clean();
			$this->assertSame( '', $output, 'Unexpected output buffer' );
			$this->assertStringContainsString( 'Best Org', $html );
			$this->assertStringContainsString( 'img4.jpg', $html );
			$this->assertStringContainsString( 'Event 2', $html );
	}
}
