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

                // By default the selected organization is valid for the current user.
                \ArtPulse\Frontend\StubState::$post_types[99]   = 'artpulse_org';
                \ArtPulse\Frontend\StubState::$post_authors[99] = 1;
        }

        public function tear_down(): void {
                $_POST               = array();
                $_FILES              = array();
                $GLOBALS['__ap_test_user_meta'] = array();
                \ArtPulse\Frontend\StubState::reset();
                parent::tear_down();
        }

        public function test_invalid_org_rejected(): void {
                // Organization belongs to another user
                \ArtPulse\Frontend\StubState::$post_authors[99] = 2;
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = true;
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = true;

                try {
                        EventSubmissionShortcode::maybe_handle_form();
                        $this->fail( 'Expected redirect' );
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'redirect', $e->getMessage() );
                }

                $this->assertSame( 'Invalid organization selected.', \ArtPulse\Frontend\StubState::$notice );
                $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
                $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );
        }

        public function test_start_date_after_end_date_rejected(): void {
                $_POST['event_start_date'] = '2024-02-01';
                $_POST['event_end_date']   = '2024-01-01';
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = true;
                \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = true;

                try {
                        EventSubmissionShortcode::maybe_handle_form();
                        $this->fail( 'Expected redirect' );
                } catch ( \RuntimeException $e ) {
                        $this->assertSame( 'redirect', $e->getMessage() );
                }

                $this->assertSame( 'Start date cannot be later than end date.', \ArtPulse\Frontend\StubState::$notice );
                $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
                $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );
        }

        public function test_banner_prepend_and_notice_on_success(): void {
                // Pretend banner uploaded and media handler returns ID 55
                // Also upload one additional image with ID 11 and place banner second in order
                \ArtPulse\Frontend\StubState::$media_returns = array(
                        'event_banner' => 55,
                        'ap_image'     => 11,
                );
                $_FILES['event_banner'] = array(
                        'name'     => 'b.jpg',
                        'tmp_name' => '/tmp/b',
                        'type'     => 'image/jpeg',
                        'error'    => 0,
                        'size'     => 1,
                );
                $_FILES['image_1'] = array(
                        'name'     => 'a.jpg',
                        'tmp_name' => '/tmp/a',
                        'type'     => 'image/jpeg',
                        'error'    => 0,
                        'size'     => 1,
                );
                $_POST['image_order'] = '11,55';

                EventSubmissionShortcode::maybe_handle_form();

                $gallery           = null;
                $banner_meta_found = false;
                foreach ( \ArtPulse\Frontend\StubState::$meta_log as $args ) {
                        if ( $args[1] === '_ap_submission_images' ) {
                                $gallery = $args[2];
                        }
                        if ( $args[1] === 'event_banner_id' && $args[2] === 55 ) {
                                $banner_meta_found = true;
                        }
                }

                $this->assertSame( array( 55, 11 ), $gallery );
                $this->assertTrue( $banner_meta_found );
                $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
        }

        public function test_first_gallery_image_used_as_banner_when_missing(): void {
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
                $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
        }

        public function test_meta_logged_when_no_images_uploaded(): void {
                EventSubmissionShortcode::maybe_handle_form();

                $gallery = $banner = null;
                foreach ( \ArtPulse\Frontend\StubState::$meta_log as $args ) {
                        if ( $args[1] === '_ap_submission_images' ) {
                                $gallery = $args[2];
                        }
                        if ( $args[1] === 'event_banner_id' ) {
                                $banner = $args[2];
                        }
                }

                $this->assertSame( array(), $gallery );
                $this->assertSame( 0, $banner );
                $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
        }
}
