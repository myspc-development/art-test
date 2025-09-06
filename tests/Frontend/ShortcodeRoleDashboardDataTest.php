<?php
namespace ArtPulse\Frontend\Tests;

use ArtPulse\Frontend\ShortcodeRoleDashboard;

/**
 * @group FRONTEND
 */
class ShortcodeRoleDashboardDataTest extends \WP_UnitTestCase {
	public function test_display_name_sanitized_and_encoded(): void {
		$raw     = 'Bad <script>alert(1)</script> "quote" & more';
		$user_id = self::factory()->user->create( array( 'display_name' => $raw ) );
		wp_set_current_user( $user_id );

		$ref    = new \ReflectionClass( ShortcodeRoleDashboard::class );
		$method = $ref->getMethod( 'script_data' );
		$method->setAccessible( true );
		$data = $method->invoke( null, 'member' );

		$expected_display = sanitize_text_field( $raw );
		$this->assertSame( $expected_display, $data['user']['displayName'] );

		wp_register_script( 'test', '' );
		wp_localize_script( 'test', 'apDashboardData', $data );
		$localized     = wp_scripts()->get_data( 'test', 'data' );
		$expected_json = wp_json_encode( $data );
		$this->assertSame( 'var apDashboardData = ' . $expected_json . ';', $localized );
	}
}
