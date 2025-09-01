<?php
namespace {
	if ( ! function_exists( 'is_user_logged_in' ) ) {
		function is_user_logged_in() {
			return true; } }
	if ( ! function_exists( 'wp_get_current_user' ) ) {
		function wp_get_current_user() {
			return new WP_User(); } }
	if ( ! class_exists( 'WP_User' ) ) {
		/**
		 * @group core
		 */
		class WP_User {
			public $roles = array(); public function __construct( $id = 0 ) {}
		} }
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $tag, $value ) {
			return $value; } }
	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $tag, $func, $priority = 10, $args = 1 ) {} }
	if ( ! function_exists( 'user_can' ) ) {
		function user_can( $user, $cap ) {
			return true; } }
	if ( ! function_exists( '__' ) ) {
		function __( $str, $domain = null ) {
			return $str; } }
	require_once __DIR__ . '/../../includes/dashboard-menu.php';
}

namespace ArtPulse\Core\Tests {
	use PHPUnit\Framework\TestCase;

	class DashboardMenuMergeTest extends TestCase {
		public function test_member_menu_contains_notifications() {
			$menu = \ap_merge_dashboard_menus( array( 'member' ), true );
			$ids  = array_column( $menu, 'id' );
			$this->assertContains( 'notifications', $ids );
		}

		public function test_member_and_artist_merge_deduplicates() {
			$menu = \ap_merge_dashboard_menus( array( 'member', 'artist' ), true );
			$ids  = array_column( $menu, 'id' );
			$this->assertContains( 'content', $ids );
			$this->assertEquals( count( $ids ), count( array_unique( $ids ) ) );
		}

		public function test_org_menu_includes_transactions() {
			$menu = \ap_merge_dashboard_menus( array( 'organization' ), true );
			$ids  = array_column( $menu, 'id' );
			$this->assertContains( 'transactions', $ids );
		}
	}
}
