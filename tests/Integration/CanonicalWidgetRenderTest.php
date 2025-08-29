<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardPresets;

class CanonicalWidgetRenderTest extends \WP_UnitTestCase {
	public static function roleProvider(): array {
		return array(
			array( 'member' ),
			array( 'artist' ),
			array( 'organization' ),
		);
	}

	/**
	 * @dataProvider roleProvider
	 */
	public function test_canonical_slugs_render_for_role( string $role ): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_query_var( 'ap_role', $role );
		$template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/simple-dashboard.php';
		ob_start();
		include $template;
		$html = ob_get_clean();
               preg_match_all( '/data-slug="([^"]+)"/', $html, $m );
               $expected = DashboardPresets::forRole( $role );
               $this->assertSame( $expected, array_values( array_unique( $m[1] ) ) );
	}
}
