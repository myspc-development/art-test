<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\NotificationHooks;
use ArtPulse\Community\NotificationManager;
use WP_UnitTestCase;

/**

 * @group COMMUNITY
 */

class MentionNotificationTest extends WP_UnitTestCase {

	private int $post_id;
	private int $author_id;
	private int $mention_id;

	public function set_up() {
		parent::set_up();
		NotificationManager::install_notifications_table();
		NotificationHooks::register();

		$this->author_id  = self::factory()->user->create( array( 'display_name' => 'Author' ) );
		$this->mention_id = self::factory()->user->create(
			array(
				'user_login'    => 'mentionuser',
				'user_nicename' => 'mentionuser',
			)
		);

		$this->post_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->author_id,
			)
		);
	}

	public function test_mention_creates_notification(): void {
		$comment_id = wp_insert_comment(
			array(
				'comment_post_ID'      => $this->post_id,
				'comment_content'      => 'Hello @mentionuser',
				'user_id'              => $this->author_id,
				'comment_author'       => 'Author',
				'comment_author_email' => 'a@test.com',
				'comment_approved'     => 1,
			)
		);

		do_action(
			'comment_post',
			$comment_id,
			1,
			array(
				'comment_post_ID' => $this->post_id,
				'comment_content' => 'Hello @mentionuser',
				'user_id'         => $this->author_id,
				'comment_author'  => 'Author',
			)
		);

		global $wpdb;
		$table = $wpdb->prefix . 'ap_notifications';
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, type FROM $table WHERE user_id = %d", $this->mention_id ), ARRAY_A );

		$this->assertNotEmpty( $row );
		$this->assertSame( 'mention', $row['type'] );
	}
}
