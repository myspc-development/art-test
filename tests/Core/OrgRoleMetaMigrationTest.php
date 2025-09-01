<?php
namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\OrgRoleMetaMigration;
	use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group core

	 */

	class OrgRoleMetaMigrationTest extends TestCase {

		private $old_wpdb;

		protected function setUp(): void {
			global $wpdb;
			MockStorage::$options   = array();
			MockStorage::$users     = array();
			MockStorage::$user_meta = array();
			$this->old_wpdb         = $wpdb;
			$wpdb                   = new class() {
				public $prefix     = 'wp_';
				public array $rows = array();
				public function prepare( $query, ...$args ) {
					return vsprintf( $query, $args ); }
				public function get_charset_collate() {
					return ''; }
				public function delete( $table, $where ) {
					$this->rows = array_filter(
						$this->rows,
						function ( $row ) use ( $where ) {
							foreach ( $where as $k => $v ) {
								if ( $row[ $k ] !== $v ) {
									return true; }
							}
							return false;
						}
					);
				}
				public function insert( $table, $data ) {
					$this->rows[] = $data; }
			};
		}

		protected function tearDown(): void {
			global $wpdb;
			$wpdb = $this->old_wpdb;
		}

		public function test_migrates_single_role_and_cleans_meta(): void {
			MockStorage::$users                              = array( 1 );
			MockStorage::$user_meta[1]['ap_organization_id'] = 10;
			MockStorage::$user_meta[1]['ap_org_role']        = 'admin';

			OrgRoleMetaMigration::maybe_migrate();

			global $wpdb;
			$this->assertSame(
				array(
					array(
						'user_id' => 1,
						'org_id'  => 10,
						'role'    => 'admin',
						'status'  => 'active',
					),
				),
				$wpdb->rows
			);
			$this->assertArrayNotHasKey( 'ap_org_role', MockStorage::$user_meta[1] );
			$this->assertSame( 1, MockStorage::$options['ap_org_roles_table_migrated'] );
		}

		public function test_migrates_roles_array_and_string(): void {
			MockStorage::$users                              = array( 2, 3 );
			MockStorage::$user_meta[2]['ap_organization_id'] = 20;
			MockStorage::$user_meta[2]['ap_org_roles']       = array( 'editor', 'curator' );
			MockStorage::$user_meta[3]['ap_organization_id'] = 30;
			MockStorage::$user_meta[3]['ap_org_roles']       = 'promoter';

			OrgRoleMetaMigration::maybe_migrate();

			global $wpdb;
			$rows = $wpdb->rows;
			$this->assertContains(
				array(
					'user_id' => 2,
					'org_id'  => 20,
					'role'    => 'editor',
					'status'  => 'active',
				),
				$rows
			);
			$this->assertContains(
				array(
					'user_id' => 2,
					'org_id'  => 20,
					'role'    => 'curator',
					'status'  => 'active',
				),
				$rows
			);
			$this->assertContains(
				array(
					'user_id' => 3,
					'org_id'  => 30,
					'role'    => 'promoter',
					'status'  => 'active',
				),
				$rows
			);
			$this->assertArrayNotHasKey( 'ap_org_roles', MockStorage::$user_meta[2] );
			$this->assertArrayNotHasKey( 'ap_org_roles', MockStorage::$user_meta[3] );
		}
	}

}
