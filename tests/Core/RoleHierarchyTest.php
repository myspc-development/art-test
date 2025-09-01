<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\RoleSetup;

if ( ! class_exists( __NAMESPACE__ . '\\WPDBStub' ) ) {
	/**
	 * @group core
	 */
	class WPDBStub {
		public $prefix     = 'wp_';
		public array $data = array();
		public function get_charset_collate() {
			return ''; }
		public function prepare( $query, ...$args ) {
			return $query; }
		public function get_var( $query ) {
			if ( str_contains( $query, 'SHOW TABLES' ) ) {
				preg_match( '/LIKE\s+(.*)/', $query, $m );
				$table = trim( $m[1], "'\"" );
				return isset( $this->data[ $table ] ) ? $table : null;
			}
			return null;
		}
		public function replace( $table, $data ) {
			$this->data[ $table ][ $data['role_key'] ] = $data;
		}
		public function get_results( $query, $format = ARRAY_A ) {
			preg_match( '/FROM\s+(\w+)/', $query, $m );
			$table = $m[1];
			return array_values( $this->data[ $table ] ?? array() );
		}
	}
}

class RoleHierarchyTest extends TestCase {

	protected function setUp(): void {
		global $wpdb;
		$wpdb = new WPDBStub();
		RoleSetup::install_roles_table();
		RoleSetup::populate_roles_table();
	}

	public function test_parent_lookup(): void {
		$this->assertSame( 'member', RoleSetup::get_parent_role( 'artist' ) );
	}

	public function test_child_lookup(): void {
		$children = RoleSetup::get_child_roles( 'member' );
		$this->assertContains( 'artist', $children );
		$this->assertContains( 'organization', $children );
	}
}
