<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';

// WordPress function stubs
if ( ! function_exists( __NAMESPACE__ . '\get_posts' ) ) {
	function get_posts( $args ) {
		return \ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$posts_return; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
		return \ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$user_meta[ $uid ][ $key ] ?? ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_list_pluck' ) ) {
	function wp_list_pluck( $input, $field ) {
		return array_map( fn( $i ) => is_object( $i ) ? $i->$field : $i[ $field ], $input ); }
}
if ( ! function_exists( __NAMESPACE__ . '\wc_add_notice' ) ) {
	function wc_add_notice( $msg, $type = '' ) {
		\ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$notice = $msg; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_die' ) ) {
	function wp_die( $msg ) {
		\ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$notice = $msg; }
}

// Minimal stubs for unused functions to avoid errors if called
if ( ! function_exists( __NAMESPACE__ . '\wp_insert_post' ) ) {
	function wp_insert_post( $arr ) {
		\ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$inserted = $arr;
		return 1; }
}
if ( ! function_exists( __NAMESPACE__ . '\media_handle_upload' ) ) {
	function media_handle_upload( $file, $post_id ) {
		return \ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$media_ids[ $file ] ?? 0; }
}
if ( ! function_exists( __NAMESPACE__ . '\set_post_thumbnail' ) ) {
	function set_post_thumbnail( $post_id, $thumb_id ) {
		\ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$thumbnail = $thumb_id; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_post_thumbnail_id' ) ) {
	function get_post_thumbnail_id( $post_id ) {
		return \ArtPulse\Frontend\Tests\EventSubmissionShortcodeTest::$thumbnail; }
}
if ( ! function_exists( __NAMESPACE__ . '\function_exists' ) ) {
	function function_exists( $name ) {
		return $name === 'wc_add_notice'; }
}

namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\EventSubmissionShortcode;

/**

 * @group FRONTEND

 */

class EventSubmissionShortcodeTest extends \WP_UnitTestCase {

	public static array $posts_return = array();
	public static array $user_meta    = array();
	public static string $notice      = '';
	public static array $inserted     = array();
	public static array $media_ids    = array();
	public static int $thumbnail      = 0;

	public function set_up(): void {
		parent::set_up();
		self::$posts_return = array();
		self::$user_meta    = array();
		self::$notice       = '';
		\ArtPulse\Frontend\StubState::reset();
		self::$inserted  = array();
		self::$media_ids = array();
		self::$thumbnail = 0;
		$_FILES          = array();

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
		$_POST              = array();
		$_FILES             = array();
		self::$posts_return = array();
		self::$user_meta    = array();
		self::$notice       = '';
		self::$inserted     = array();
		self::$media_ids    = array();
		self::$thumbnail    = 0;
		parent::tear_down();
	}

	public function test_invalid_org_rejected(): void {
		// Authorized org id is 5, selected org 99 should fail
		self::$user_meta[1]['ap_organization_id'] = 5;
		self::$posts_return                       = array();

		EventSubmissionShortcode::maybe_handle_form();

		$this->assertSame( 'Invalid organization selected.', self::$notice );
		$this->assertEmpty( self::$inserted );
	}

	public function test_start_date_after_end_date_rejected(): void {
		// Valid organization to avoid org failure
		self::$user_meta[1]['ap_organization_id'] = 99;

		$_POST['event_start_date'] = '2024-02-01';
		$_POST['event_end_date']   = '2024-01-01';

		EventSubmissionShortcode::maybe_handle_form();

		$this->assertSame( 'Start date cannot be later than end date.', self::$notice );
		$this->assertEmpty( self::$inserted );
	}

	public function test_banner_included_in_submission_images(): void {
		// Valid organization
		self::$user_meta[1]['ap_organization_id'] = 99;

		// Pretend banner uploaded and media handler returns ID 55
		self::$media_ids        = array( 'event_banner' => 55 );
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
		self::$user_meta[1]['ap_organization_id'] = 99;

		// Upload a single additional image
		self::$media_ids   = array( 'ap_image' => 11 );
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
		$this->assertSame( 11, self::$thumbnail );
	}
}
