<?php
use PHPUnit\Framework\TestCase;

/**

 * @group CORE
 */

class AvatarHttpsFixTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		// Minimal filter system for testing
		if ( ! function_exists( 'add_filter' ) ) {
			function add_filter( $hook, $callback, $priority = 10 ) {
				global $wp_filters;
				$wp_filters[ $hook ][ $priority ][] = $callback;
			}
			function apply_filters( $hook, $value ) {
				global $wp_filters;
				if ( isset( $wp_filters[ $hook ] ) ) {
					ksort( $wp_filters[ $hook ] );
					foreach ( $wp_filters[ $hook ] as $callbacks ) {
						foreach ( $callbacks as $cb ) {
							$value = $cb( $value );
						}
					}
				}
				return $value;
			}
		}

		if ( ! function_exists( 'set_url_scheme' ) ) {
			function set_url_scheme( $url, $scheme ) {
				return preg_replace( '#^https?:#', $scheme . ':', $url );
			}
		}

		require __DIR__ . '/../includes/avatar-https-fix.php';
	}

	protected function tearDown(): void {
		global $wp_filters;
		$wp_filters = array();
		parent::tearDown();
	}

	public function test_get_avatar_url_is_forced_to_https(): void {
		$result = apply_filters( 'get_avatar_url', 'http://example.com/avatar.jpg' );
		$this->assertSame( 'https://example.com/avatar.jpg', $result );
	}

	public function test_wp_get_attachment_url_is_forced_to_https(): void {
		$result = apply_filters( 'wp_get_attachment_url', 'http://example.com/wp-content/uploads/image.jpg' );
		$this->assertSame( 'https://example.com/wp-content/uploads/image.jpg', $result );
	}

	public function test_simple_local_avatar_url_is_forced_to_https(): void {
		$result = apply_filters( 'simple_local_avatar_url', 'http://example.com/local-avatar.jpg' );
		$this->assertSame( 'https://example.com/local-avatar.jpg', $result );
	}
}
