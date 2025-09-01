<?php
/**
 * @group wpunit
 */
class LoggingTest extends WP_UnitTestCase {
	private string $file;
	private $prev;

	protected function setUp(): void {
		parent::setUp();
		$this->file = tempnam( sys_get_temp_dir(), 'ap' );
		$this->prev = ini_get( 'error_log' );
		ini_set( 'error_log', $this->file );
	}

	protected function tearDown(): void {
		if ( $this->prev !== false && $this->prev !== '' ) {
			ini_set( 'error_log', $this->prev );
		} else {
			ini_restore( 'error_log' );
		}
		@unlink( $this->file );
		unset( $GLOBALS['ap_debug_override'] );
		parent::tearDown();
	}

	public function test_logs_when_override_true(): void {
		$GLOBALS['ap_debug_override'] = true;
		ap_log( 'hello' );
		$this->assertStringContainsString( 'hello', file_get_contents( $this->file ) );
	}

	public function test_silent_when_override_false(): void {
		$GLOBALS['ap_debug_override'] = false;
		ap_log( 'secret' );
		$this->assertSame( '', file_get_contents( $this->file ) );
	}
}
