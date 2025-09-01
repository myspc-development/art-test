<?php
namespace ArtPulse\Reporting;

/**

 * @group CORE

 */

class SnapshotBuilder {
	public static string $called = '';
	public static function generate_csv( array $args ): string {
		self::$called = 'csv';
		return __FILE__ . '.csv'; }
	public static function generate_pdf( array $args ): string {
		self::$called = 'pdf';
		return __FILE__ . '.pdf'; }
}
class GrantReportBuilder {
	public static bool $called = false;
	public static function generate_pdf( array $args ): string {
		self::$called = true;
		return __FILE__ . '.grant.pdf'; }
}
namespace ArtPulse\Core\Tests;

use ArtPulse\Core\ReportSubscriptionManager;
use WP_UnitTestCase;

class ReportSnapshotHookTest extends WP_UnitTestCase {
	public function test_send_reports_triggers_snapshot_builder(): void {
		global $wpdb;
		ReportSubscriptionManager::install_table();
		$table = $wpdb->prefix . 'ap_org_report_subscriptions';
		$wpdb->insert(
			$table,
			array(
				'org_id'      => 1,
				'email'       => 'test@example.com',
				'frequency'   => 'weekly',
				'format'      => 'csv',
				'report_type' => 'engagement',
			)
		);
		ReportSubscriptionManager::send_weekly_reports();
		$this->assertSame( 'csv', \ArtPulse\Reporting\SnapshotBuilder::$called );
	}
}
