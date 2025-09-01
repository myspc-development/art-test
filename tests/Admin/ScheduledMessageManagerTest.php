<?php
namespace ArtPulse\Admin\Tests;

use WP_UnitTestCase;
use ArtPulse\Admin\ScheduledMessageManager;
use ArtPulse\Admin\OrgCommunicationsCenter;

/**
 * @group ADMIN
 */
class ScheduledMessageManagerTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		ScheduledMessageManager::install_scheduled_table();
		OrgCommunicationsCenter::install_messages_table();
	}

	public function test_schedule_and_process(): void {
		$org_id = self::factory()->post->create( array( 'post_type' => 'artpulse_org' ) );
		$sender = self::factory()->user->create();
		update_post_meta( $org_id, 'ap_follower_ids', array( $sender ) );

		$send_at = time() - 10; // already due
		$id      = ScheduledMessageManager::schedule_message( $org_id, $sender, 'Hi', 'Test', $send_at );
		$this->assertGreaterThan( 0, $id );

		ScheduledMessageManager::process_due_messages();

		global $wpdb;
		$msg_table = $wpdb->prefix . 'ap_org_messages';
		$rows      = $wpdb->get_results( "SELECT * FROM $msg_table", ARRAY_A );
		$this->assertCount( 1, $rows );
		$this->assertSame( 'Hi', $rows[0]['subject'] );
	}
}
