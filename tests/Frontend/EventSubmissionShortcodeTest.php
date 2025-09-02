<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\EventSubmissionShortcode;

/**

 * @group FRONTEND

 */

class EventSubmissionShortcodeTest extends \WP_UnitTestCase {

        public function set_up(): void {
                parent::set_up();
                \ArtPulse\Frontend\StubState::reset();
                \ArtPulse\Frontend\StubState::$current_user = 1;
                $GLOBALS['__ap_test_user_meta'] = array();
                \ArtPulse\Frontend\StubState::$function_exists_map = array( 'wc_add_notice' => true );
                \ArtPulse\Frontend\StubState::$notice       = '';
                \ArtPulse\Frontend\StubState::$inserted_post = array();
                \ArtPulse\Frontend\StubState::$media_returns = array();
                \ArtPulse\Frontend\StubState::$thumbnail     = 0;
                \ArtPulse\Frontend\StubState::$get_posts_return = array();
                $_FILES = array();

                if ( ! is_dir( ABSPATH . '/wp-admin/includes' ) ) {
                        mkdir( ABSPATH . '/wp-admin/includes', 0777, true );
                        file_put_contents( ABSPATH . '/wp-admin/includes/image.php', '<?php' );
                        file_put_contents( ABSPATH . '/wp-admin/includes/file.php', '<?php' );
                        file_put_contents( ABSPATH . '/wp-admin/includes/media.php', '<?php' );
                }

                // Required POST fields
                $_POST = array(
                        'ap_submit_event'   => 1,
                        'ap_event_nonce'    => 'nonce',
                        'event_title'       => 'title',
                        'event_description' => 'desc',
                        'event_date'        => '2024-01-01',
                        'event_location'    => '',
                        'event_org'         => 99,
                );
        }

        public function tear_down(): void {
                $_POST               = array();
                $_FILES              = array();
                $GLOBALS['__ap_test_user_meta'] = array();
                \ArtPulse\Frontend\StubState::reset();
                parent::tear_down();
        }

	public function test_invalid_org_rejected(): void {
                // Authorized org id is 5, selected org 99 should fail
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 5;
                \ArtPulse\Frontend\StubState::$get_posts_return = array();

		EventSubmissionShortcode::maybe_handle_form();

                $this->assertSame( 'Invalid organization selected.', \ArtPulse\Frontend\StubState::$notice );
                $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
	}

	public function test_start_date_after_end_date_rejected(): void {
		// Valid organization to avoid org failure
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 99;

                $_POST['event_start_date'] = '2024-02-01';
                $_POST['event_end_date']   = '2024-01-01';

		EventSubmissionShortcode::maybe_handle_form();

                $this->assertSame( 'Start date cannot be later than end date.', \ArtPulse\Frontend\StubState::$notice );
                $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
	}

	public function test_banner_included_in_submission_images(): void {
		// Valid organization
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 99;

		// Pretend banner uploaded and media handler returns ID 55
                \ArtPulse\Frontend\StubState::$media_returns = array( 'event_banner' => 55 );
		$_FILES['event_banner'] = array(
			'name'     => 'b.jpg',
			'tmp_name' => '/tmp/b',
			'type'     => 'image/jpeg',
			'error'    => 0,
			'size'     => 1,
		);

		EventSubmissionShortcode::maybe_handle_form();

		$gallery = null;
		foreach ( \ArtPulse\Frontend\StubState::$meta_log as $args ) {
			if ( $args[1] === '_ap_submission_images' ) {
				$gallery = $args[2];
			}
		}

                $this->assertSame( array( 55 ), $gallery );
	}

	public function test_first_gallery_image_used_as_banner_when_missing(): void {
		// Valid organization
                $GLOBALS['__ap_test_user_meta'][1]['ap_organization_id'] = 99;

		// Upload a single additional image
                \ArtPulse\Frontend\StubState::$media_returns   = array( 'ap_image' => 11 );
		$_FILES['image_1'] = array(
			'name'     => 'a.jpg',
			'tmp_name' => '/tmp/a',
			'type'     => 'image/jpeg',
			'error'    => 0,
			'size'     => 1,
		);

		EventSubmissionShortcode::maybe_handle_form();

		$gallery           = null;
		$banner_meta_found = false;
		foreach ( \ArtPulse\Frontend\StubState::$meta_log as $args ) {
			if ( $args[1] === '_ap_submission_images' ) {
				$gallery = $args[2];
			}
			if ( $args[1] === 'event_banner_id' && $args[2] === 11 ) {
				$banner_meta_found = true;
			}
		}

                $this->assertSame( array( 11 ), $gallery );
                $this->assertTrue( $banner_meta_found );
                $this->assertSame( 11, \ArtPulse\Frontend\StubState::$thumbnail );
	}
}
