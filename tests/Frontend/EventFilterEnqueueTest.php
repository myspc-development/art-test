<?php
declare(strict_types=1);

namespace ArtPulse\Frontend\Tests {

	use PHPUnit\Framework\TestCase;
	use Brain\Monkey;
	use Brain\Monkey\Functions;
	use ArtPulse\Frontend\EventFilter;

	/**
	 * @group FRONTEND
	 */
	class EventFilterEnqueueTest extends TestCase {
		private array $scripts   = array();
		private array $styles    = array();
		private array $localized = array();

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();

			if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
				define( 'ARTPULSE_PLUGIN_FILE', __FILE__ );
			}

			// Basic path and URL helpers.
			Functions\when( 'plugin_dir_url' )->alias( fn( $f ) => 'https://example.test/p/' );
			Functions\when( 'admin_url' )->alias( fn( $p = '' ) => 'https://example.test/' . ltrim( $p, '/' ) );
			Functions\when( 'wp_create_nonce' )->alias( fn( $a ) => 'nonce' );

			// Capture enqueued assets and localization.
			Functions\when( 'wp_enqueue_script' )->alias(
				function ( $handle ) {
					$this->scripts[] = $handle;
				}
			);
			Functions\when( 'wp_enqueue_style' )->alias(
				function ( $handle ) {
					$this->styles[] = $handle;
				}
			);
			Functions\when( 'wp_localize_script' )->alias(
				function ( $handle, $name, $data ) {
					$this->localized = array(
						'handle' => $handle,
						'name'   => $name,
						'data'   => $data,
					);
				}
			);
			Functions\when( 'ap_enqueue_global_styles' )->justReturn( null );
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		public function test_enqueue_adds_assets_and_localization(): void {
			EventFilter::enqueue();

			$this->assertSame( array( 'ap-event-filter-form' ), $this->styles );
			$this->assertSame( array( 'ap-event-filter' ), $this->scripts );
			$this->assertSame( 'ap-event-filter', $this->localized['handle'] );
			$this->assertArrayHasKey( 'ajaxurl', $this->localized['data'] );
			$this->assertArrayHasKey( 'nonce', $this->localized['data'] );
		}
	}

}
