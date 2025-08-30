<?php
namespace ArtPulse\Tests;

trait AjaxTestHelper {
	protected function make_admin_user(): int {
		return self::factory()->user->create( array( 'role' => 'administrator' ) );
	}
	protected function set_nonce( string $action, string $key = '_ajax_nonce' ): void {
		$_POST[ $key ]    = wp_create_nonce( $action );
		$_REQUEST[ $key ] = $_POST[ $key ];
	}
	protected function reset_superglobals(): void {
		$_GET = $_POST = $_REQUEST = array();
	}
}
