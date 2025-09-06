<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Community\DirectMessages;
use ArtPulse\Tests\Email;

/**
 * @group REST
 */
class DirectMessagesTest extends \WP_UnitTestCase {

	private int $user1;
	private int $user2;
	private string $nonce;

	public static function setUpBeforeClass(): void {
			parent::setUpBeforeClass();
			Email::install();
	}

	public function set_up() {
			parent::set_up();
			DirectMessages::install_table();
			DirectMessages::install_flags_table();
			DirectMessages::register();
			do_action( 'rest_api_init' );

			$this->user1 = self::factory()->user->create( array( 'user_email' => 'u1@test.com' ) );
			$this->user2 = self::factory()->user->create( array( 'user_email' => 'u2@test.com' ) );

			wp_set_current_user( $this->user1 );
			$user = new \WP_User( $this->user1 );
			$user->add_cap( 'ap_send_messages' );
			$this->nonce = wp_create_nonce( 'wp_rest' );
	}

	public function tear_down() {
			Email::clear();
			parent::tear_down();
	}

	public function test_send_and_fetch_message(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'Hello' );
		$post->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$row   = $wpdb->get_row( "SELECT * FROM $table", ARRAY_A );
		$this->assertSame( $this->user1, (int) $row['sender_id'] );
		$this->assertSame( $this->user2, (int) $row['recipient_id'] );
		$this->assertSame( 'Hello', $row['content'] );
		$this->assertSame( '0', $row['is_read'] );

				$this->assertCount( 1, Email::messages() );

		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/messages' );
		$get->set_param( 'with', $this->user2 );
		$get->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( 'Hello', $data[0]['content'] );
	}

	public function test_send_rejects_invalid_nonce(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'Hello' );
		$post->set_param( 'nonce', 'bad' );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_list_conversations_and_mark_read(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'Hi there' );
		$post->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table  = $wpdb->prefix . 'ap_messages';
		$row    = $wpdb->get_row( "SELECT * FROM $table", ARRAY_A );
		$msg_id = (int) $row['id'];

		$convos = new \WP_REST_Request( 'GET', '/artpulse/v1/conversations' );
		$convos->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $convos );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame(
			array(
				array(
					'user_id' => $this->user2,
					'unread'  => 1,
				),
			),
			$res->get_data()
		);

		wp_set_current_user( $this->user2 );
		$read = new \WP_REST_Request( 'POST', '/artpulse/v1/message/read' );
		$read->set_param( 'ids', array( $msg_id ) );
		$read->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $read );
		$this->assertSame( 200, $res->get_status() );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $msg_id ), ARRAY_A );
		$this->assertSame( '1', $row['is_read'] );
	}

	public function test_context_and_block(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'Context hello' );
		$post->set_param( 'context_type', 'artwork' );
		$post->set_param( 'context_id', 55 );
		$post->set_param( 'nonce', $this->nonce );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/messages/context/artwork/55' );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );

		$block = new \WP_REST_Request( 'POST', '/artpulse/v1/messages/block' );
		$block->set_param( 'user_id', $this->user2 );
		$res = rest_get_server()->dispatch( $block );
		$this->assertSame( 200, $res->get_status() );
	}

	public function test_send_v2_updates_seen_and_search(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages/send' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'Hey, I love your recent artwork!' );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'Hey, I love your recent artwork!', $data['content'] );

		$updates = new \WP_REST_Request( 'GET', '/artpulse/v1/messages/updates' );
		$updates->set_param( 'since', '1970-01-01 00:00:00' );
		$res = rest_get_server()->dispatch( $updates );
		$this->assertSame( 200, $res->get_status() );
		$list = $res->get_data();
		$this->assertNotEmpty( $list );
		$msg_id = $list[0]['id'];

		$seen = new \WP_REST_Request( 'POST', '/artpulse/v1/messages/seen' );
		$seen->set_param( 'message_ids', array( $msg_id ) );
		$res = rest_get_server()->dispatch( $seen );
		$this->assertSame( 200, $res->get_status() );

		$search = new \WP_REST_Request( 'GET', '/artpulse/v1/messages/search' );
		$search->set_param( 'q', 'recent artwork' );
		$res = rest_get_server()->dispatch( $search );
		$this->assertSame( 200, $res->get_status() );
		$found = $res->get_data();
		$this->assertNotEmpty( $found );
	}

	public function test_send_v2_with_parent_attachments_and_tags(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/messages/send' );
		$post->set_param( 'recipient_id', $this->user2 );
		$post->set_param( 'content', 'extras' );
		$post->set_param( 'parent_id', 9 );
		$post->set_param( 'attachments', array( 5, '6' ) );
				$post->set_param( 'tags', array( 'widget_foo', 'bar' ) );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_messages';
		$row   = $wpdb->get_row( "SELECT * FROM $table ORDER BY id DESC", ARRAY_A );
		$this->assertSame( '9', $row['parent_id'] );
		$this->assertSame( '5,6', $row['attachments'] );
				$this->assertSame( 'widget_foo,bar', $row['tags'] );
	}
}
