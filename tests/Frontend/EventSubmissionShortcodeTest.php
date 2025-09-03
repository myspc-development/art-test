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
               \ArtPulse\Frontend\StubState::$get_posts_return = array(
                       (object) array(
                               'ID'          => 99,
                               'post_type'   => 'artpulse_org',
                               'post_author' => 1,
                       ),
               );
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

        public function test_redirect_occurs_when_org_invalid(): void {
                // Organization belongs to another user
               \ArtPulse\Frontend\StubState::$post_authors[99] = 2;
               \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = false;
               \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = false;
               \ArtPulse\Frontend\StubState::$function_exists_map['ArtPulse\\Frontend\\wp_safe_redirect'] = true;
               \ArtPulse\Frontend\StubState::$function_exists_map['ArtPulse\\Frontend\\wp_get_referer']    = true;

               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( 'Invalid organization selected.', \ArtPulse\Frontend\StubState::$notice );
                       $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );
               }
       }

        public function test_redirect_occurs_on_invalid_date_range(): void {
               $_POST['event_start_date'] = '2024-02-01';
               $_POST['event_end_date']   = '2024-01-01';
               \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = false;
               \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = false;
               \ArtPulse\Frontend\StubState::$function_exists_map['ArtPulse\\Frontend\\wp_safe_redirect'] = true;
               \ArtPulse\Frontend\StubState::$function_exists_map['ArtPulse\\Frontend\\wp_get_referer']    = true;

               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( 'Start date cannot be later than end date.', \ArtPulse\Frontend\StubState::$notice );
                       $this->assertEmpty( \ArtPulse\Frontend\StubState::$inserted_post );
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );
               }
       }

        public function test_redirect_occurs_after_successful_upload(): void {
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

               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );

                       $gallery_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === '_ap_submission_images'
                       );
                       $banner_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === 'event_banner_id'
                       );

                       $this->assertCount( 1, $gallery_calls );
                       $this->assertSame( array( 55, 11 ), array_values( $gallery_calls )[0][2] );
                       $this->assertCount( 1, $banner_calls );
                       $this->assertSame( 55, array_values( $banner_calls )[0][2] );
                       $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
               }
       }

       public function test_meta_logged_when_banner_missing(): void {
                // Upload a single additional image
                \ArtPulse\Frontend\StubState::$media_returns   = array( 'ap_image' => 11 );
                $_FILES['image_1'] = array(
                        'name'     => 'a.jpg',
                        'tmp_name' => '/tmp/a',
                        'type'     => 'image/jpeg',
                        'error'    => 0,
                        'size'     => 1,
                );

               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );

                       $gallery_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === '_ap_submission_images'
                       );
                       $banner_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === 'event_banner_id'
                       );

                       $this->assertCount( 1, $gallery_calls );
                       $this->assertSame( array( 11 ), array_values( $gallery_calls )[0][2] );
                       $this->assertCount( 1, $banner_calls );
                       $this->assertSame( 11, array_values( $banner_calls )[0][2] );
                       $this->assertSame( 11, \ArtPulse\Frontend\StubState::$thumbnail );
                       $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
               }
       }

        public function test_meta_logged_when_no_images_uploaded(): void {
               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );

                       $gallery_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === '_ap_submission_images'
                       );
                       $banner_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === 'event_banner_id'
                       );

                       $this->assertCount( 1, $gallery_calls );
                       $this->assertSame( array(), array_values( $gallery_calls )[0][2] );
                       $this->assertCount( 1, $banner_calls );
                       $this->assertSame( 0, array_values( $banner_calls )[0][2] );
                       $this->assertSame( 'Event submitted successfully!', \ArtPulse\Frontend\StubState::$notice );
               }
       }

       public function test_redirect_occurs_after_failed_upload(): void {
               \ArtPulse\Frontend\StubState::$media_returns = array(
                       'event_banner' => 55,
                       'ap_image'     => new \WP_Error( 'upload_error', 'bad' ),
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

               \ArtPulse\Frontend\StubState::$function_exists_map['wp_safe_redirect'] = true;
               \ArtPulse\Frontend\StubState::$function_exists_map['wp_get_referer']    = true;

               $this->expectException( \RuntimeException::class );
               $this->expectExceptionMessage( 'redirect' );

               try {
                       EventSubmissionShortcode::maybe_handle_form();
               } finally {
                       $this->assertSame( '/referer', \ArtPulse\Frontend\StubState::$page );

                       $gallery_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === '_ap_submission_images'
                       );
                       $banner_calls = array_filter(
                               \ArtPulse\Frontend\StubState::$meta_log,
                               static fn( $args ) => $args[1] === 'event_banner_id'
                       );

                       $this->assertCount( 1, $gallery_calls );
                       $this->assertSame( array( 55 ), array_values( $gallery_calls )[0][2] );
                       $this->assertCount( 1, $banner_calls );
                       $this->assertSame( 55, array_values( $banner_calls )[0][2] );
                       $this->assertSame( 'Error uploading additional image. Please try again.', \ArtPulse\Frontend\StubState::$notice );
               }
       }
}
