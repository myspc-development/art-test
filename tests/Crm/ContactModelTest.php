<?php
namespace ArtPulse\Crm;

if ( ! function_exists( __NAMESPACE__ . '\sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return $email; }
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_text_field' ) ) {
	function sanitize_text_field( $text ) {
		return $text; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data ); }
}
if ( ! function_exists( __NAMESPACE__ . '\current_time' ) ) {
	function current_time( $type = 'mysql' ) {
		return ContactModelTest::$now; }
}

use PHPUnit\Framework\TestCase;

/**

 * @group CRM
 */

class ContactModelTest extends TestCase {

	public static string $now;
	private $wpdb;

	protected function setUp(): void {
		self::$now  = '2025-01-01 00:00:00';
		$this->wpdb = new class() {
			public string $prefix     = 'wp_';
			public array $rows        = array();
			public array $prepared    = array();
			public string $last_query = '';
			public function esc_like( $text ) {
				return $text; }
			public function prepare( $sql, ...$args ) {
				$this->prepared[] = $sql;
				foreach ( $args as $arg ) {
					$sql = preg_replace( '/%[ds]/', is_numeric( $arg ) ? $arg : "'$arg'", $sql, 1 );
				}
				return $sql;
			}
			public function get_row( $sql ) {
				if ( preg_match( "/org_id = (\\d+) AND email = '([^']+)'/", $sql, $m ) ) {
					foreach ( $this->rows as $row ) {
						if ( $row['org_id'] == (int) $m[1] && $row['email'] === $m[2] ) {
							return (object) $row;
						}
					}
				}
				return null;
			}
			public function insert( $table, $data ) {
				$data['id']   = count( $this->rows ) + 1;
				$this->rows[] = $data; }
			public function update( $table, $data, $where ) {
				foreach ( $this->rows as &$row ) {
					if ( $row['id'] == $where['id'] ) {
						$row = array_merge( $row, $data ); }
				} }
			public function get_results( $sql, $format = ARRAY_A ) {
				$this->last_query = $sql;
				return array(); }
		};
		global $wpdb;
		$wpdb = $this->wpdb;
	}

	public function test_add_or_update_creates_and_updates(): void {
		ContactModel::add_or_update( 1, 'a@example.com', 'Alice', array( 'follower' ) );
		$this->assertCount( 1, $this->wpdb->rows );
		$row = $this->wpdb->rows[0];
		$this->assertSame( 'Alice', $row['name'] );
		$this->assertSame( '["follower"]', $row['tags'] );

		ContactModel::add_or_update( 1, 'a@example.com', '', array( 'donor' ) );
		$this->assertCount( 1, $this->wpdb->rows );
		$row  = $this->wpdb->rows[0];
		$tags = json_decode( $row['tags'], true );
		sort( $tags );
		$this->assertSame( array( 'donor', 'follower' ), $tags );
	}

	public function test_add_tag_appends_tag(): void {
		ContactModel::add_or_update( 1, 'b@example.com', 'Bob', array( 'rsvp' ) );
		ContactModel::add_tag( 1, 'b@example.com', 'supporter' );
		$row  = $this->wpdb->rows[0];
		$tags = json_decode( $row['tags'], true );
		sort( $tags );
		$this->assertSame( array( 'rsvp', 'supporter' ), $tags );
	}

	public function test_get_all_uses_like_placeholder(): void {
		ContactModel::get_all( 1, 'friend' );
		$found = false;
		foreach ( $this->wpdb->prepared as $q ) {
			if ( strpos( $q, 'LIKE %s' ) !== false ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found );
	}
}
