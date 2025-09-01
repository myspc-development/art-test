<?php
namespace ArtPulse\Monetization;

if ( ! function_exists( __NAMESPACE__ . '\current_time' ) ) {
	function current_time( string $type = 'mysql' ) {
		return EventBoostManagerTest::$nowFormatted;
	}
}

use PHPUnit\Framework\TestCase;

/**

 * @group MONETIZATION

 */

class EventBoostManagerTest extends TestCase {

	public static int $now;
	public static string $nowFormatted;
	private $wpdb;

	protected function setUp(): void {
		self::$now          = time();
		self::$nowFormatted = date( 'Y-m-d H:i:s', self::$now );
		$this->wpdb         = new class() {
			public string $prefix = 'wp_';
			public array $data    = array();
			public function insert( $table, $data ) {
				$this->data[ $table ][] = $data; }
			public function prepare( $sql, ...$args ) {
				foreach ( $args as $arg ) {
					$sql = preg_replace( '/%[ds]/', is_numeric( $arg ) ? $arg : "'$arg'", $sql, 1 );
				}
				return $sql;
			}
			public function get_var( $query ) {
				if ( preg_match( "/FROM (\\w+) WHERE post_id = (\\d+) AND expires_at > '([^']+)'/", $query, $m ) ) {
					[$all, $table, $event, $ts] = $m;
					$count                      = 0;
					foreach ( $this->data[ $table ] ?? array() as $row ) {
						if ( $row['post_id'] == (int) $event && $row['expires_at'] > $ts ) {
							++$count;
						}
					}
					return $count;
				}
				return 0;
			}
		};
		global $wpdb;
		$wpdb = $this->wpdb;
	}

	public function test_record_boost_inserts_row(): void {
		EventBoostManager::record_boost( 5, 2, 10.0, 'stripe' );
		$table = $this->wpdb->prefix . 'ap_event_boosts';
		$this->assertCount( 1, $this->wpdb->data[ $table ] );
		$row = $this->wpdb->data[ $table ][0];
		$this->assertSame( 5, $row['post_id'] );
		$this->assertSame( 2, $row['user_id'] );
		$this->assertSame( 10.0, $row['amount'] );
		$this->assertSame( 'stripe', $row['method'] );
	}

	public function test_is_boosted_checks_expiry(): void {
		EventBoostManager::record_boost( 5, 2, 10.0, 'stripe' );
		$this->assertTrue( EventBoostManager::is_boosted( 5 ) );
		self::$now         += 8 * 86400;
		self::$nowFormatted = date( 'Y-m-d H:i:s', self::$now );
		$this->assertFalse( EventBoostManager::is_boosted( 5 ) );
	}
}
