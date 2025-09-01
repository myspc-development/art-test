<?php
namespace ArtPulse\Frontend\Tests {

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
			$this->assertStringContainsString( 'Opening Hours', $html );
			$this->assertStringContainsString( 'Monday', $html );
			$this->assertStringContainsString( '09:00 - 17:00', $html );
		}
	}
}
