<?php
namespace ArtPulse\Admin;

// Stub WordPress and plugin functions
if ( ! function_exists( __NAMESPACE__ . '\\wp_enqueue_script' ) ) {
	function wp_enqueue_script( ...$args ) {
		\ArtPulse\Admin\Tests\EnqueueAssetsTest::$scripts[] = $args; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_enqueue_style' ) ) {
	function wp_enqueue_style( ...$args ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\\get_current_screen' ) ) {
	function get_current_screen() {
		return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$current_screen; }
}
if ( ! function_exists( __NAMESPACE__ . '\\plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return '/'; }
}
if ( ! function_exists( __NAMESPACE__ . '\\plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return '/'; }
}
if ( ! function_exists( __NAMESPACE__ . '\\file_exists' ) ) {
	function file_exists( $path ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\rest_url' ) ) {
	function rest_url( $path = '' ) {
		return $path; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_create_nonce' ) ) {
	function wp_create_nonce( $action = '' ) {
		return 'nonce'; }
}
if ( ! function_exists( __NAMESPACE__ . '\\is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_current_user_id' ) ) {
	function get_current_user_id() {
		return 1; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_user_meta' ) ) {
	function get_user_meta( $uid, $key, $single = false ) {
		return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$user_meta[ $uid ][ $key ] ?? ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_posts' ) ) {
	function get_posts( $args = array() ) {
		return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$posts; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_the_terms' ) ) {
	function get_the_terms( $post_id, $tax ) {
		return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$terms[ $post_id ] ?? false; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_localize_script' ) ) {
	function wp_localize_script( $handle, $name, $data ) {
		\ArtPulse\Admin\Tests\EnqueueAssetsTest::$localized        = $data;
		\ArtPulse\Admin\Tests\EnqueueAssetsTest::$localize_calls[] = array( $handle, $name, $data );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\get_option' ) ) {
	function get_option( $key, $default = array() ) {
		return \ArtPulse\Admin\Tests\EnqueueAssetsTest::$options[ $key ] ?? $default; }
}
if ( ! function_exists( __NAMESPACE__ . '\\update_option' ) ) {
	function update_option( $key, $value ) {
		\ArtPulse\Admin\Tests\EnqueueAssetsTest::$options[ $key ] = $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_roles' ) ) {
	function wp_roles() {
		return (object) array(
			'roles' => array(
				'administrator' => array(),
				'subscriber'    => array(),
			),
		); }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_script_is' ) ) {
	function wp_script_is( $h, $list ) {
		return false; }
}

namespace ArtPulse\Core;

// Stub WordPress functions used by Plugin::get_event_submission_url
if ( ! function_exists( __NAMESPACE__ . '\\get_posts' ) ) {
	function get_posts( $args = array() ) {
		return array( (object) array( 'ID' => 1 ) ); }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_permalink' ) ) {
	function get_permalink( $id ) {
		return '/submit'; }
}
if ( ! function_exists( __NAMESPACE__ . '\\home_url' ) ) {
	function home_url( $path = '/' ) {
		return '/'; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\EnqueueAssets;
use Brain\Monkey;
use Brain\Monkey\Functions;

class EnqueueAssetsTest extends TestCase {

	public static array $localized      = array();
	public static array $user_meta      = array();
	public static array $posts          = array();
	public static array $terms          = array();
	public static array $scripts        = array();
	public static array $localize_calls = array();
	public static array $options        = array();
	public static $current_screen       = null;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'admin_url' )->alias( fn( $path = '' ) => $path );

		self::$localized      = array();
		self::$user_meta      = array();
		self::$posts          = array();
		self::$terms          = array();
		self::$scripts        = array();
		self::$localize_calls = array();
		self::$options        = array( 'artpulse_settings' => array( 'disable_styles' => true ) );
		self::$current_screen = null;
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public static function add_post( int $id, string $title, string $stage_slug, string $stage_name ): void {
		self::$posts[]      = (object) array(
			'ID'         => $id,
			'post_title' => $title,
		);
		self::$terms[ $id ] = array(
			(object) array(
				'slug' => $stage_slug,
				'name' => $stage_name,
			),
		);
	}

	public function test_localizes_stage_groups(): void {
		self::$user_meta[1]['ap_organization_id'] = 5;
		self::add_post( 1, 'Art One', 'stage-1', 'Stage 1' );
		EnqueueAssets::enqueue_frontend();
		$this->assertArrayHasKey( 'projectStages', self::$localized );
		$this->assertSame( 'Stage 1', self::$localized['projectStages'][0]['name'] );
	}

	public function test_block_editor_scripts_enqueue_when_screen_available(): void {
		self::$current_screen = (object) array(
			'id'              => 'edit-artpulse_event',
			'is_block_editor' => true,
		);
		EnqueueAssets::enqueue_block_editor_assets();
		$handles = array_map( fn( $a ) => $a[0] ?? '', self::$scripts );
		$this->assertContains( 'ap-event-gallery', $handles );
	}

	public function test_exits_gracefully_without_screen(): void {
		self::$current_screen = null;
		EnqueueAssets::enqueue_block_editor_assets();
		$this->assertSame( array(), self::$scripts );
	}
}
