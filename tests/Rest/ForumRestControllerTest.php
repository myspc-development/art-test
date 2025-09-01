<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Community\ForumRestController;
use ArtPulse\Community\CommunityRoles;

/**
 * @group REST
 */
class ForumRestControllerTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $thread_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create();
		update_user_meta( $this->user_id, 'community_role', CommunityRoles::VERIFIED_ARTIST );
		wp_set_current_user( $this->user_id );

		$this->thread_id = wp_insert_post(
			array(
				'post_type'    => 'ap_forum_thread',
				'post_title'   => 'Sample',
				'post_content' => 'Body',
				'post_status'  => 'publish',
				'post_author'  => $this->user_id,
			)
		);

		ForumRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_list_and_create_threads(): void {
		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/forum/threads' );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( 'Sample', $data[0]['title'] );

		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/forum/threads' );
		$post->set_body_params(
			array(
				'title'   => 'New Thread',
				'content' => 'Hello',
			)
		);
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );
		$id = $res->get_data()['id'];
		$this->assertNotEmpty( $id );
		$this->assertSame( 'New Thread', get_post( $id )->post_title );
	}

	public function test_comment_flow(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/forum/thread/' . $this->thread_id . '/comments' );
		$post->set_param( 'content', 'Test comment' );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );
		$comment_id = $res->get_data()['id'];

		wp_update_comment(
			array(
				'comment_ID'       => $comment_id,
				'comment_approved' => 1,
			)
		);

		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/forum/thread/' . $this->thread_id . '/comments' );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( 'Test comment', $data[0]['content'] );
	}
}
