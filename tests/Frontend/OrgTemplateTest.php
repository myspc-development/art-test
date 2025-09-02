<?php
namespace ArtPulse\Frontend {
        function ap_render_favorite_button( int $object_id, string $object_type = '' ): string {
                return '';
        }

        function ap_share_buttons( ...$args ): string {
                return '';
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

                public function test_loop_outputs_content_when_have_posts_true(): void {
                        MockStorage::$have_posts = true;
                        ob_start();
                        include __DIR__ . '/../../templates/salient/content-artpulse_org.php';
                        $html = ob_get_clean();
                        $this->assertFalse( MockStorage::$have_posts );
                        $this->assertStringContainsString( 'Org Content', $html );
                }
        }
}
