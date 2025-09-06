<?php
namespace ArtPulse\Frontend {
	if ( ! function_exists( __NAMESPACE__ . '\\ap_render_favorite_button' ) ) {
		function ap_render_favorite_button( int $object_id, string $object_type = '' ): string {
				return '';
		}
	}

	if ( ! function_exists( __NAMESPACE__ . '\\ap_share_buttons' ) ) {
		function ap_share_buttons( ...$args ): string {
				return '';
		}
	}
}

namespace ArtPulse\Frontend\Tests {

		require_once __DIR__ . '/../TestStubs.php';

		use PHPUnit\Framework\TestCase;
		use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group FRONTEND
	 */

	class OrgTemplateTest extends TestCase {

		protected function setUp(): void {
			MockStorage::$have_posts = true;
			MockStorage::$post_meta  = array();
		}

		public function test_opening_hours_displayed(): void {
				MockStorage::$post_meta = array(
					'ead_org_street_address'    => '123',
					'ead_org_monday_start_time' => '09:00',
					'ead_org_monday_end_time'   => '17:00',
				);
				ob_start();
				include __DIR__ . '/../../templates/salient/content-artpulse_org.php';
				$html = ob_get_clean();
				$this->assertFalse( MockStorage::$have_posts );
				$this->assertStringContainsString( 'Opening Hours', $html );
				$this->assertStringContainsString( 'Monday', $html );
				$this->assertStringContainsString( '09:00 - 17:00', $html );
		}

		public function test_address_and_website_displayed(): void {
				MockStorage::$post_meta = array(
					'ead_org_street_address' => '123 Main St',
					'ead_org_website_url'    => 'https://example.com',
				);
				ob_start();
				include __DIR__ . '/../../templates/salient/content-artpulse_org.php';
				$html = ob_get_clean();
				$this->assertFalse( MockStorage::$have_posts );
				$this->assertStringContainsString( 'Address:', $html );
				$this->assertStringContainsString( '123 Main St', $html );
				$this->assertStringContainsString( 'Website:', $html );
				$this->assertStringContainsString( 'https://example.com', $html );
		}

		public function test_output_unchanged_for_single_post(): void {
				MockStorage::$have_posts = true;
				ob_start();
				include __DIR__ . '/../../templates/salient/content-artpulse_org.php';
				$html = ob_get_clean();
				$this->assertFalse( MockStorage::$have_posts );
				$expected = '<div class="nectar-portfolio-single-media">' . PHP_EOL
						. '      </div>' . PHP_EOL
						. '  <h1 class="entry-title">Test Org</h1>' . PHP_EOL
						. '    <div class="entry-content">Org Content</div>';
				$this->assertSame( $expected, trim( $html ) );
		}
	}
}
