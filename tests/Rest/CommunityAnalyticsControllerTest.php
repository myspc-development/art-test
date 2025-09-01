<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CommunityAnalyticsController;
use ArtPulse\Community\DirectMessages;
use ArtPulse\Community\CommentReports;
use ArtPulse\Community\BlockedUsers;

/**
 * @group REST
 */
class CommunityAnalyticsControllerTest extends \WP_UnitTestCase {

	private int $user1;
	private int $user2;
	private int $thread_id;

	public function set_up() {
		parent::set_up();
		DirectMessages::install_table();
		DirectMessages::install_flags_table();
		BlockedUsers::install_table();
		CommentReports::install_table();
		CommunityAnalyticsController::register();
		do_action( 'rest_api_init' );

		$this->user1     = self::factory()->user->create();
		$this->user2     = self::factory()->user->create();
		$this->thread_id = self::factory()->post->create( array( 'post_type' => 'ap_forum_thread' ) );
	}

	public function test_messaging_endpoint(): void {
		DirectMessages::add_message( $this->user1, $this->user2, 'hi' );
		BlockedUsers::add( $this->user1, $this->user2 );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/analytics/community/messaging' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 1, $data['total'] );
		$this->assertSame( 1, $data['blocked_count'] );
	}

	public function test_comments_endpoint(): void {
		$post = self::factory()->post->create( array( 'post_type' => 'artpulse_artwork' ) );
		$cid  = wp_insert_comment(
			array(
				'comment_post_ID'  => $post,
				'comment_content'  => 'Nice',
				'user_id'          => $this->user1,
				'comment_approved' => 1,
			)
		);
		CommentReports::add_report( $cid, $this->user2 );
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/analytics/community/comments' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 1, $data['total'] );
		$this->assertSame( 1, $data['flagged_count'] );
	}

	public function test_forums_endpoint(): void {
		wp_insert_comment(
			array(
				'comment_post_ID'  => $this->thread_id,
				'comment_content'  => 'Reply',
				'comment_approved' => 1,
			)
		);
		wp_set_current_user( $this->user1 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/analytics/community/forums' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertNotEmpty( $data['threads_created'] );
		$this->assertNotEmpty( $data['top_threads'] );
	}
}
