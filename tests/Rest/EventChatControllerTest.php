<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\DB\Chat;

/**
 * @group REST
 */
class EventChatControllerTest extends \WP_UnitTestCase {

	private int $event;
	private int $user;

	public function set_up() {
		parent::set_up();
		do_action( 'init' );
		do_action( 'rest_api_init' );

		$this->event = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		$this->user  = self::factory()->user->create();
		update_post_meta( $this->event, 'event_rsvp_list', array( $this->user ) );
		wp_set_current_user( $this->user );
	}

	public function test_post_and_get_chat(): void {
		Chat\install_tables();
		$nonce = wp_create_nonce( 'wp_rest' );
		$post  = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event . '/chat' );
		$post->set_body_params( array( 'content' => 'Hello' ) );
		$post->set_header( 'X-WP-Nonce', $nonce );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );
		$msg = $res->get_data();

		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event . '/chat' );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( 'Hello', $data[0]['content'] );

		$react = new \WP_REST_Request( 'POST', '/artpulse/v1/chat/' . $msg['id'] . '/reaction' );
		$react->set_body_params( array( 'emoji' => '❤️' ) );
		$react->set_header( 'X-WP-Nonce', $nonce );
		$res = rest_get_server()->dispatch( $react );
		$this->assertSame( 200, $res->get_status() );

		$res  = rest_get_server()->dispatch( $get );
		$data = $res->get_data();
		$this->assertSame( 1, $data[0]['reactions']['❤️'] );
	}

	public function test_post_chat_without_nonce_fails(): void {
		Chat\install_tables();
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/event/' . $this->event . '/chat' );
		$post->set_body_params( array( 'content' => 'Hello' ) );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_add_reaction_without_nonce_fails(): void {
		Chat\install_tables();
		$msg_id = Chat\insert_message( $this->event, $this->user, 'Hi' );
		$react  = new \WP_REST_Request( 'POST', '/artpulse/v1/chat/' . $msg_id . '/reaction' );
		$react->set_body_params( array( 'emoji' => '❤️' ) );
		$res = rest_get_server()->dispatch( $react );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_get_chat_without_preloading_helpers(): void {
		$get = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event . '/chat' );
		$res = rest_get_server()->dispatch( $get );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array(), $res->get_data() );
	}
}
