<?php
declare(strict_types=1);

namespace ArtPulse\Tests\Widgets {
	use PHPUnit\Framework\TestCase;
	use Brain\Monkey;
	use Brain\Monkey\Functions;

	abstract class WidgetTestCase extends TestCase {

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();
			Functions\when( '__' )->returnArg();
			Functions\when( 'esc_html__' )->returnArg();
			Functions\when( 'esc_html_e' )->alias(
				static function ( $text ): void {
					echo $text;
				}
			);
			Functions\when( 'esc_attr' )->returnArg();
			Functions\when( 'esc_url_raw' )->returnArg();
			Functions\when( 'rest_url' )->returnArg();
			Functions\when( 'wp_create_nonce' )->justReturn( 'nonce' );
			Functions\when( 'is_user_logged_in' )->justReturn( true );
			Functions\when( 'user_can' )->justReturn( true );
			Functions\when( 'date_i18n' )->alias( static fn( $format, $timestamp ) => date( $format, $timestamp ) );
			Functions\when( 'wp_get_current_user' )->alias(
				static fn() => (object) array(
					'display_name' => 'Test User',
					'user_login'   => 'test',
				)
			);
			Functions\when( 'wp_kses_post' )->returnArg();
			Functions\when( 'get_current_user_id' )->justReturn( 1 );
			Functions\when( 'get_user_meta' )->alias( static fn( $user_id, $key, $single = true ) => null );
			Functions\when( 'sanitize_title' )->alias( static fn( $title ) => strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ) ) );
			Functions\when( 'get_option' )->alias( static fn( $key ) => $key === 'date_format' ? 'Y-m-d' : null );
			Functions\when( 'get_permalink' )->alias( static fn( $id ) => 'http://example.com/?p=' . $id );
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}
	}
}

namespace ArtPulse\Core {
	interface DashboardWidgetInterface {}

	class DashboardWidgetRegistry {

		private static array $widgets = array();

		public static function register( $id, $label = '', $icon = '', $description = '', $callback = null, $args = array() ): void {
			self::$widgets[ $id ] = array(
				'id'       => $id,
				'callback' => $callback,
				'args'     => $args,
			);
		}

		public static function get_widget( $id ) {
			return self::$widgets[ $id ] ?? null;
		}

		public static function get_widget_callback( $id ) {
			return self::$widgets[ $id ]['callback'] ?? null;
		}

		public static function exists( $id ): bool {
			return isset( self::$widgets[ $id ] );
		}

		public static function reset(): void {
			self::$widgets = array();
		}
	}

	class DashboardController {

		public static function get_role( $user_id ): string {
			return 'member';
		}
	}

	class ActivityLogger {

		public static function get_logs( $org_id, $user_id, int $limit = 10 ): array {
			return array(
				(object) array(
					'description' => 'log',
					'logged_at'   => date( 'Y-m-d H:i:s' ),
				),
			);
		}
	}
}
