<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\FeedbackRestController;
use ArtPulse\Core\FeedbackManager;

/**
 * @group REST
 */
class FeedbackRestControllerTest extends \WP_UnitTestCase {

	private int $user;

	public function set_up() {
		parent::set_up();
		FeedbackManager::install_table();
		FeedbackRestController::register();
		do_action( 'rest_api_init' );
		$this->user = self::factory()->user->create();
		wp_set_current_user( $this->user );
	}

	public function test_submit_vote_and_comment(): void {
		// Submit
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/feedback' );
		$post->set_body_params(
			array(
				'type'        => 'bug',
				'description' => 'Broken',
				'email'       => 'a@b.com',
			)
		);
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_feedback';
		$id    = (int) $wpdb->get_var( "SELECT id FROM $table LIMIT 1" );

		// Vote
		$vote = new \WP_REST_Request( 'POST', "/artpulse/v1/feedback/$id/vote" );
		$res  = rest_get_server()->dispatch( $vote );
		$this->assertSame( 200, $res->get_status() );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT votes FROM $table WHERE id=%d", $id ) );
		$this->assertSame( '1', $row->votes );

		// Comment
		$comment = new \WP_REST_Request( 'POST', "/artpulse/v1/feedback/$id/comments" );
		$comment->set_body_params( array( 'comment' => 'Nice' ) );
		$res = rest_get_server()->dispatch( $comment );
		$this->assertSame( 200, $res->get_status() );
		$ctable = $wpdb->prefix . 'ap_feedback_comments';
		$count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $ctable WHERE feedback_id = $id" );
		$this->assertSame( 1, $count );
	}
}
