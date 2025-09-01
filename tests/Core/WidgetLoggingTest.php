<?php
namespace {
	// Simple hook system to mimic WordPress behavior.
	if ( ! isset( $GLOBALS['ap_wp_hooks'] ) ) {
		$GLOBALS['ap_wp_hooks'] = array(
			'actions' => array(),
			'filters' => array(),
		);
	}

	if ( ! function_exists( 'add_filter' ) ) {
		function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
			$GLOBALS['ap_wp_hooks']['filters'][ $hook ][] = $callback;
		}
	}
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $hook, $value ) {
			foreach ( $GLOBALS['ap_wp_hooks']['filters'][ $hook ] ?? array() as $cb ) {
				$value = $cb( $value );
			}
			return $value;
		}
	}
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
			$GLOBALS['ap_wp_hooks']['actions'][ $hook ][] = $callback;
		}
	}
	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $hook, ...$args ) {
			foreach ( $GLOBALS['ap_wp_hooks']['actions'][ $hook ] ?? array() as $cb ) {
				$cb( ...$args );
			}
		}
	}
	if ( ! function_exists( '__return_true' ) ) {
		function __return_true() {
			return true; }
	}
	if ( ! function_exists( 'wp_upload_dir' ) ) {
		function wp_upload_dir() {
			return array( 'basedir' => $GLOBALS['ap_wp_upload_dir'] );
		}
	}
}

namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use function ArtPulse\Tests\safe_unlink;
	use function ArtPulse\Tests\rm_rf;

	/**

	 * @group core

	 */

	class WidgetLoggingTest extends TestCase {

		private string $uploadsDir;
		private string $logFile;

		protected function setUp(): void {
			parent::setUp();
			$GLOBALS['ap_wp_hooks'] = array(
				'actions' => array(),
				'filters' => array(),
			);
			if ( ! defined( 'ABSPATH' ) ) {
				define( 'ABSPATH', __DIR__ );
			}

			$this->uploadsDir = sys_get_temp_dir() . '/ap_uploads_' . uniqid();
			mkdir( $this->uploadsDir );
			$GLOBALS['ap_wp_upload_dir'] = $this->uploadsDir;
			$this->logFile               = $this->uploadsDir . '/ap-widget.log';

			add_filter( 'ap_enable_widget_logging', '__return_true' );
			include __DIR__ . '/../../includes/widget-logging.php';
		}

		protected function tearDown(): void {
			safe_unlink( $this->logFile );
			rm_rf( $this->uploadsDir );
			parent::tearDown();
		}

		public function test_widget_events_are_logged(): void {
			do_action( 'ap_widget_rendered', 'widget_id', 1 );
			do_action( 'ap_widget_hidden', 'widget_id', 1 );

			$this->assertFileExists( $this->logFile );
			$log = file_get_contents( $this->logFile );
			$this->assertStringContainsString( 'Widget widget_id rendered for user 1', $log );
			$this->assertStringContainsString( 'Widget widget_id hidden for user 1', $log );
		}

		public function test_log_level_filter_is_respected(): void {
			add_filter( 'ap_widget_log_level', fn() => 'error' );
			do_action( 'ap_widget_rendered', 'widget_id', 1 );
			$this->assertFileDoesNotExist( $this->logFile );
		}
	}

}
