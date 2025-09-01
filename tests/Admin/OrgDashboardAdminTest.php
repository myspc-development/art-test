<?php
namespace ArtPulse\Admin;

// Stub WordPress functions and constants
if ( ! function_exists( __NAMESPACE__ . '\\add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\\remove_menu_page' ) ) {
	function remove_menu_page( $slug ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\\add_menu_page' ) ) {
	function add_menu_page( ...$args ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_transient' ) ) {
	function get_transient( $key ) {
		return \ArtPulse\Admin\Tests\OrgDashboardAdminStub::$transients[ $key ] ?? false; }
}
if ( ! function_exists( __NAMESPACE__ . '\\set_transient' ) ) {
	function set_transient( $key, $value, $expire = 0 ) {
		\ArtPulse\Admin\Tests\OrgDashboardAdminStub::$transients[ $key ] = $value;
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\delete_transient' ) ) {
	function delete_transient( $key ) {
		unset( \ArtPulse\Admin\Tests\OrgDashboardAdminStub::$transients[ $key ] );
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_posts' ) ) {
	function get_posts( $args ) {
		return \ArtPulse\Admin\Tests\OrgDashboardAdminStub::get_posts( $args ); }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_is_post_revision' ) ) {
	function wp_is_post_revision( $id ) {
		return false; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_post_meta' ) ) {
	function get_post_meta( $post_id, $key, $single = false ) {
		return \ArtPulse\Admin\Tests\OrgDashboardAdminStub::get_post_meta( $post_id, $key ); }
}

/**

 * @group admin

 */

class WP_Post {
	public $post_type;
	public $ID;
	public function __construct( string $post_type = 'post', int $ID = 0 ) {
		$this->post_type = $post_type;
		$this->ID        = $ID;
	}
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\OrgDashboardAdmin;

class OrgDashboardAdminStub {
	public static array $transients    = array();
	public static array $posts_return  = array();
	public static int $get_posts_calls = 0;
	public static array $post_meta     = array();

	public static function reset(): void {
		self::$transients      = array();
		self::$posts_return    = array();
		self::$get_posts_calls = 0;
		self::$post_meta       = array();
	}

	public static function get_posts( array $args ): array {
		++self::$get_posts_calls;
		$type = $args['post_type'] ?? 'post';
		return self::$posts_return[ $type ] ?? array();
	}

	public static function get_post_meta( int $post_id, string $key ) {
		return self::$post_meta[ $post_id ][ $key ] ?? '';
	}
}

class OrgDashboardAdminTest extends TestCase {

	protected function setUp(): void {
		OrgDashboardAdminStub::reset();
	}

	public function test_get_all_orgs_caches_results(): void {
		OrgDashboardAdminStub::$posts_return['artpulse_org'] = array( (object) array( 'ID' => 1 ) );
		$ref = new \ReflectionClass( OrgDashboardAdmin::class );
		$m   = $ref->getMethod( 'get_all_orgs' );
		$m->setAccessible( true );

		$first  = $m->invoke( null );
		$second = $m->invoke( null );

		$this->assertSame( $first, $second );
		$this->assertSame( 1, OrgDashboardAdminStub::$get_posts_calls );
	}

	public function test_get_org_posts_caches_results(): void {
		OrgDashboardAdminStub::$posts_return['ap_profile_link'] = array( (object) array( 'ID' => 2 ) );
		$ref = new \ReflectionClass( OrgDashboardAdmin::class );
		$m   = $ref->getMethod( 'get_org_posts' );
		$m->setAccessible( true );

		$args   = array(
			'post_type'   => 'ap_profile_link',
			'numberposts' => 1,
		);
		$first  = $m->invoke( null, 5, 'profile_links', $args );
		$second = $m->invoke( null, 5, 'profile_links', $args );

		$this->assertSame( $first, $second );
		$this->assertSame( 1, OrgDashboardAdminStub::$get_posts_calls );
	}

	public function test_clear_cache_deletes_transient_on_event_save(): void {
		OrgDashboardAdminStub::$transients['ap_dash_profile_links_10']  = array( 'a' );
		OrgDashboardAdminStub::$transients['ap_dash_artworks_10']       = array( 'b' );
		OrgDashboardAdminStub::$transients['ap_dash_events_10']         = array( 'c' );
		OrgDashboardAdminStub::$transients['ap_dash_stats_artworks_10'] = array( 'd' );
		OrgDashboardAdminStub::$transients['ap_org_metrics_10']         = array( 'e' );
		OrgDashboardAdminStub::$post_meta[5]['org_id']                  = 10;

		$post = new \WP_Post( 'artpulse_event', 5 );
		$ref  = new \ReflectionClass( OrgDashboardAdmin::class );
		$m    = $ref->getMethod( 'clear_cache' );
		$m->setAccessible( true );

		$m->invoke( null, 5, $post, true );

		$this->assertArrayNotHasKey( 'ap_dash_profile_links_10', OrgDashboardAdminStub::$transients );
		$this->assertArrayNotHasKey( 'ap_dash_artworks_10', OrgDashboardAdminStub::$transients );
		$this->assertArrayNotHasKey( 'ap_dash_events_10', OrgDashboardAdminStub::$transients );
		$this->assertArrayNotHasKey( 'ap_dash_stats_artworks_10', OrgDashboardAdminStub::$transients );
		$this->assertArrayNotHasKey( 'ap_org_metrics_10', OrgDashboardAdminStub::$transients );
	}
}
