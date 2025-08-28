<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryRoleFilterTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		foreach ( array( 'member', 'artist', 'administrator', 'organization' ) as $role ) {
			if ( ! get_role( $role ) ) {
				add_role( $role, ucfirst( $role ) );
			}
		}
	}

	public function test_get_widgets_filters_by_role(): void {
		DashboardWidgetRegistry::register( 'alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
		DashboardWidgetRegistry::register( 'beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'administrator' ) ) );

		$member_id = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $member_id );
		$member = DashboardWidgetRegistry::get_widgets( 'member' );

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$admin = DashboardWidgetRegistry::get_widgets( 'administrator' );

		$this->assertArrayHasKey( 'alpha', $member );
		$this->assertArrayNotHasKey( 'beta', $member );
		$this->assertArrayHasKey( 'beta', $admin );
	}

	public function test_get_widgets_respects_current_user_role_when_multiple_requested(): void {
		DashboardWidgetRegistry::register( 'alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
		DashboardWidgetRegistry::register( 'beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'artist' ) ) );

		$member_id = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $member_id );
		$combined = DashboardWidgetRegistry::get_widgets( array( 'member', 'artist' ) );

		$this->assertArrayHasKey( 'alpha', $combined );
		$this->assertArrayNotHasKey( 'beta', $combined );
	}

	public function test_get_widgets_no_duplicate_when_roles_overlap(): void {
		DashboardWidgetRegistry::register( 'shared', 'Shared', '', '', '__return_null', array( 'roles' => array( 'member', 'artist' ) ) );

		$member_id = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $member_id );
		$widgets = DashboardWidgetRegistry::get_widgets( array( 'member', 'artist' ) );
		$this->assertCount( 1, array_filter( array_keys( $widgets ), fn( $id ) => $id === 'shared' ) );
	}

	public function test_get_widgets_by_role_filters(): void {
		DashboardWidgetRegistry::register( 'alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
		DashboardWidgetRegistry::register( 'beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'artist' ) ) );

		$member_id = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $member_id );
		$member = DashboardWidgetRegistry::get_widgets_by_role( 'member' );

		$artist_id = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $artist_id );
		$artist = DashboardWidgetRegistry::get_widgets_by_role( 'artist' );

		$this->assertArrayHasKey( 'alpha', $member );
		$this->assertArrayNotHasKey( 'beta', $member );
		$this->assertArrayHasKey( 'beta', $artist );
	}

	public function test_get_widgets_by_role_full_visibility(): void {
		DashboardWidgetRegistry::register(
			'gamma',
			'Gamma',
			'',
			'',
			'__return_null',
			array( 'roles' => array( 'member', 'artist', 'organization' ) )
		);

		$member_id = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $member_id );
		$member = DashboardWidgetRegistry::get_widgets_by_role( 'member' );

		$artist_id = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $artist_id );
		$artist = DashboardWidgetRegistry::get_widgets_by_role( 'artist' );

		$org_id = self::factory()->user->create( array( 'role' => 'organization' ) );
		wp_set_current_user( $org_id );
		$org = DashboardWidgetRegistry::get_widgets_by_role( 'organization' );

		$this->assertArrayHasKey( 'gamma', $member );
		$this->assertArrayHasKey( 'gamma', $artist );
		$this->assertArrayHasKey( 'gamma', $org );
	}
}
