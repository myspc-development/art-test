<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\DashboardRoleRewrite;
use ArtPulse\Core\DashboardWidgetRegistry;

/**

 * @group FRONTEND

 */

class DashboardRoleTemplateTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Clear widget registry to keep output predictable
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		set_query_var( 'ap_dashboard_role', null );
		set_query_var( 'ap_dashboard', null );
	}

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
        public function test_renders_when_dashboard_role_query_var_present(): void {
		if ( ! get_role( 'artist' ) ) {
			add_role( 'artist', 'Artist' );
		}
		$uid = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $uid );

		set_query_var( 'ap_dashboard_role', 1 );

		$this->expectOutputRegex( '/<section[^>]*class="[^"]*ap-role-layout[^"]*(?:ap-dashboard-grid[^"]*)?"[^>]*data-role="artist"[^>]*>/s' );
		DashboardRoleRewrite::maybe_render();
}

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
        public function test_renders_when_dashboard_query_var_present(): void {
		if ( ! get_role( 'organization' ) ) {
			add_role( 'organization', 'Organization' );
		}
		$uid = self::factory()->user->create( array( 'role' => 'organization' ) );
		wp_set_current_user( $uid );

		set_query_var( 'ap_dashboard', 1 );

		$this->expectOutputRegex( '/<section[^>]*class="[^"]*ap-role-layout[^"]*(?:ap-dashboard-grid[^"]*)?"[^>]*data-role="organization"[^>]*>/s' );
		DashboardRoleRewrite::maybe_render();
}
}
