<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;

/**

 * @group core

 */

class MultiOrgRolesTest extends TestCase {

	private $old_wpdb;

	protected function setUp(): void {
		global $wpdb;
		$this->old_wpdb = $wpdb;
		$wpdb           = new class() {
			public $prefix     = 'wp_';
			public array $rows = array();
			public function prepare( $query, ...$args ) {
				return vsprintf( $query, $args );
			}
			public function get_var( $query ) {
				if ( preg_match( '/user_id = (\d+)/', $query, $mu ) && preg_match( '/org_id = (\d+)/', $query, $mo ) ) {
					$uid  = (int) $mu[1];
					$org  = (int) $mo[1];
					$role = null;
					if ( preg_match( '/role = ([^ ]+)/', $query, $mr ) ) {
						$role = trim( $mr[1], "'" );
					}
					$count = 0;
					foreach ( $this->rows as $r ) {
						if ( $r['user_id'] === $uid && $r['org_id'] === $org && ( $role === null || $r['role'] === $role ) ) {
							++$count;
						}
					}
					return $count;
				}
				return 0;
			}
			public function get_charset_collate() {
				return '';
			}
			public function delete( $table, $where ) {
				$this->rows = array_filter(
					$this->rows,
					function ( $row ) use ( $where ) {
						foreach ( $where as $k => $v ) {
							if ( $row[ $k ] !== $v ) {
								return true;
							}
						}
						return false;
					}
				);
			}
			public function insert( $table, $data ) {
				$this->rows[] = $data;
			}
		};
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb = $this->old_wpdb;
	}

	public function test_has_role_true(): void {
		global $wpdb;
		$wpdb->rows[] = array(
			'user_id' => 2,
			'org_id'  => 5,
			'role'    => 'curator',
		);
		$this->assertTrue( \ArtPulse\Core\ap_user_has_org_role( 2, 5, 'curator' ) );
	}

	public function test_has_role_false(): void {
		global $wpdb;
		$wpdb->rows[] = array(
			'user_id' => 2,
			'org_id'  => 5,
			'role'    => 'curator',
		);
		$this->assertFalse( \ArtPulse\Core\ap_user_has_org_role( 2, 5, 'admin' ) );
	}
}
