<?php
namespace ArtPulse\Community\Tests;

use WP_UnitTestCase;
use ArtPulse\Community\FollowManager;
use ArtPulse\Community\NotificationManager;

/**

 * @group COMMUNITY
 */

class FollowManagerTest extends WP_UnitTestCase {

	private int $follower_id;
	private int $followee_id;

	public function set_up() {
		parent::set_up();
		FollowManager::install_follows_table();
		NotificationManager::install_notifications_table();

		$this->follower_id = self::factory()->user->create();
		$this->followee_id = self::factory()->user->create();
	}

	public function test_following_user_creates_notification(): void {
		FollowManager::add_follow( $this->follower_id, $this->followee_id, 'user' );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_notifications';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT user_id, type, object_id, related_id FROM $table WHERE user_id = %d",
				$this->followee_id
			),
			ARRAY_A
		);

		$this->assertNotEmpty( $row );
		$this->assertSame( 'follower', $row['type'] );
		$this->assertSame( $this->followee_id, (int) $row['user_id'] );
		$this->assertSame( $this->followee_id, (int) $row['object_id'] );
		$this->assertSame( $this->follower_id, (int) $row['related_id'] );
	}
}
