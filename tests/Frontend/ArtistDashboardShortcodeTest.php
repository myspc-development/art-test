<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';
require_once __DIR__ . '/../TestHelpers.php';

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\ArtistDashboardShortcode;

/**

 * @group FRONTEND

 */

class ArtistDashboardShortcodeTest extends TestCase {

        protected function setUp(): void {
                \ArtPulse\Frontend\StubState::reset();
                \ArtPulse\Frontend\StubState::$current_user = 1;
                \ArtPulse\Frontend\StubState::$get_posts_return = array(
                        (object) array(
                                'ID'         => 5,
                                'post_title' => 'Art One',
                        ),
                );
                \ArtPulse\Frontend\StubState::$shortcodes = array(
                        '[ap_user_profile]' => '<div class="ap-user-profile"></div>',
                );
        }

        protected function tearDown(): void {
                $_POST = array();
                \ArtPulse\Frontend\StubState::reset();
                parent::tearDown();
        }

	public function test_delete_button_rendered(): void {
		$html = ArtistDashboardShortcode::render();
		$this->assertStringContainsString( 'ap-delete-artwork', $html );
                $this->assertStringContainsString( 'ap-user-profile', $html );
	}

        public function test_deletion_returns_ordered_html(): void {
                \ArtPulse\Frontend\StubState::$get_posts_return = array(
                        (object) array(
                                'ID'         => 1,
                                'post_title' => 'First',
                        ),
                        (object) array(
                                'ID'         => 3,
                                'post_title' => 'Second',
                        ),
                );
                $_POST['artwork_id'] = 2;
                $_POST['nonce']      = 'n';
                \ArtPulse\Frontend\StubState::$post_types[2] = 'artpulse_artwork';

                ArtistDashboardShortcode::handle_ajax_delete_artwork();

                $this->assertSame( 2, \ArtPulse\Frontend\StubState::$deleted_post );
                $this->assertSame( 'menu_order', \ArtPulse\Frontend\StubState::$get_posts_args['orderby'] ?? null );
                $this->assertSame( 'ASC', \ArtPulse\Frontend\StubState::$get_posts_args['order'] ?? null );

                $html = \ArtPulse\Frontend\StubState::$json['updated_list_html'] ?? '';
                $pos1 = strpos( $html, 'First' );
                $pos2 = strpos( $html, 'Second' );
                $this->assertNotFalse( $pos1 );
                $this->assertNotFalse( $pos2 );
                $this->assertLessThan( $pos2, $pos1 );
        }
}
