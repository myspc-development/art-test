<?php
namespace ArtPulse\Core\Tests;

use ArtPulse\Core\ReportSubscriptionManager;

/**
 * @group CORE
 */
class ReportSubscriptionManagerTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		ReportSubscriptionManager::register();
	}

	public function test_cron_scheduled(): void {
		ReportSubscriptionManager::schedule_cron();
		$this->assertNotFalse( wp_next_scheduled( 'ap_weekly_org_reports' ) );
		$this->assertNotFalse( wp_next_scheduled( 'ap_monthly_org_reports' ) );
	}
}
