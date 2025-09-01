<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';
if ( ! function_exists( __NAMESPACE__ . '\get_page_by_path' ) ) {
	function get_page_by_path( $path, $output = null, $type = null ) {
		return \ArtPulse\Frontend\Tests\OrgPublicProfileShortcodeTest::$page;}
}
if ( ! function_exists( __NAMESPACE__ . '\wp_get_attachment_url' ) ) {
	function wp_get_attachment_url( $id ) {
		return 'img' . $id . '.jpg';}
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrgPublicProfileShortcode;

/**

 * @group frontend

 */

class OrgPublicProfileShortcodeTest extends TestCase {

	public static $page = null;

	protected function setUp(): void {
		\ArtPulse\Frontend\StubState::reset();
		\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_profile_published', '1' );
		\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_tagline', 'Best Org' );
		\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_theme_color', '#abc' );
		\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_logo_id', 4 );
		\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_banner_id', 5 );
		\ArtPulse\Frontend\update_post_meta( 1, 'ead_org_description', 'About us' );
		\ArtPulse\Frontend\update_post_meta( 1, 'ap_org_featured_events', '2,3' );
		self::$page = null;
	}

	public function test_render_outputs_tagline(): void {
		$html = OrgPublicProfileShortcode::render( array( 'id' => 1 ) );
		$this->assertStringContainsString( 'Best Org', $html );
		$this->assertStringContainsString( 'img4.jpg', $html );
		$this->assertStringContainsString( 'Event 2', $html );
	}
}
