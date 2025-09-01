<?php
namespace ArtPulse\Admin;

if ( ! function_exists( __NAMESPACE__ . '\\wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return true;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\get_post_meta' ) ) {
	function get_post_meta( $post_id, $key, $single = false ) {
		return \ArtPulse\Admin\Tests\MetaBoxesArtworkTest::$meta[ $post_id ][ $key ] ?? ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\\update_post_meta' ) ) {
	function update_post_meta( $post_id, $key, $value ) {
		\ArtPulse\Admin\Tests\MetaBoxesArtworkTest::$updated[ $post_id ][ $key ] = $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\\sanitize_text_field' ) ) {
	function sanitize_text_field( $v ) {
		return $v; }
}
if ( ! function_exists( __NAMESPACE__ . '\\sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $v ) {
		return $v; }
}
if ( ! function_exists( __NAMESPACE__ . '\\current_time' ) ) {
	function current_time( $type ) {
		return 'now'; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\MetaBoxesArtwork;

/**

 * @group admin

 */

class MetaBoxesArtworkTest extends TestCase {

	public static array $meta    = array();
	public static array $updated = array();

	protected function setUp(): void {
		self::$meta    = array();
		self::$updated = array();
		$_POST         = array();
	}

	protected function tearDown(): void {
		$_POST         = array();
		self::$meta    = array();
		self::$updated = array();
		parent::tearDown();
	}

	public function test_price_history_recorded_when_price_changes(): void {
		$_POST['ead_artwork_meta_nonce_field'] = 'nonce';
		$_POST['artwork_price']                = '200';

		self::$meta[5]['artwork_price'] = '100';

		$post = (object) array( 'post_type' => 'artpulse_artwork' );
		MetaBoxesArtwork::save_artwork_meta( 5, $post );

		$this->assertArrayHasKey( 'price_history', self::$updated[5] );
		$history = self::$updated[5]['price_history'];
		$this->assertSame( '100', $history[0]['price'] );
		$this->assertSame( 'now', $history[0]['date'] );
	}
}
