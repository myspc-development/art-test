<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\OrgRolesController;

/**
 * @group REST
 */
class OrgRolesControllerPermissionTest extends \WP_UnitTestCase {

	private int $admin;
	private int $subscriber;
	private string $table;
	private OrgRolesController $controller;

	public function set_up() {
		parent::set_up();
		global $wpdb;
		$this->table = $wpdb->prefix . 'ap_roles';
		$charset     = $wpdb->get_charset_collate();
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$this->table} (
            role_key varchar(191) NOT NULL,
            parent_role_key varchar(191) NULL,
            display_name varchar(191) NOT NULL,
            PRIMARY KEY (role_key)
        ) $charset;"
		);
		$wpdb->insert(
			$this->table,
			array(
				'role_key'        => 'test_role',
				'parent_role_key' => null,
				'display_name'    => 'Test Role',
			)
		);

		$this->admin      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$this->controller = new OrgRolesController();
		$this->controller->register_routes();
	}

	public function tear_down() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$this->table}" );
		parent::tear_down();
	}

	public function test_get_roles_requires_authentication(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/roles' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_get_roles_requires_manage_options_capability(): void {
		wp_set_current_user( $this->subscriber );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/roles' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_get_roles_returns_data_for_admin(): void {
		wp_set_current_user( $this->admin );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/roles' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
		$first = $data[0];
		$this->assertArrayHasKey( 'role_key', $first );
		$this->assertArrayHasKey( 'display_name', $first );
		$this->assertArrayHasKey( 'parent_role_key', $first );
	}
}
