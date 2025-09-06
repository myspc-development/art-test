<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\ActivityLogger;

/**

 * @group CORE
 */

class ActivityLoggerTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		ActivityLogger::install_table();
	}

	public function test_log_inserts_row(): void {
		global $wpdb;
		ActivityLogger::log( null, 1, 'test', 'Testing log', array( 'a' => 'b' ) );
		$table = $wpdb->prefix . 'ap_activity_logs';
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		$this->assertSame( 1, $count );
	}
}
