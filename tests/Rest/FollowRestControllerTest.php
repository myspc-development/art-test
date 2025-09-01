<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Community\FollowRestController;
use ArtPulse\Community\FollowManager;

/**
 * @group REST
 */
class FollowRestControllerTest extends \WP_UnitTestCase {

	private int $user1;
	private int $user2;
	private int $event1;
	private int $event2;

	public function set_up() {
		parent::set_up();
		FollowManager::install_follows_table();

		$this->user1 = self::factory()->user->create();
		$this->user2 = self::factory()->user->create();

		$this->event1 = wp_insert_post(
			array(
				'post_title'  => 'Event 1',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->user2,
			)
		);
		$this->event2 = wp_insert_post(
			array(
				'post_title'  => 'Event 2',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->user2,
			)
		);

		FollowRestController::register();
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->user1 );
	}

	public function test_add_follow_creates_record_and_updates_meta(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$req->set_param( 'post_id', $this->event1 );
		$req->set_param( 'post_type', 'artpulse_event' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'following', $data['status'] );
		$this->assertContains( $this->event1, $data['follows'] );
		$this->assertSame( array( $this->event1 ), get_user_meta( $this->user1, '_ap_follows', true ) );
		$this->assertSame( 1, (int) get_user_meta( $this->user1, 'ap_following_count', true ) );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE user_id = %d AND object_id = %d",
				$this->user1,
				$this->event1
			)
		);
		$this->assertSame( 1, $count );
	}

	public function test_remove_follow_deletes_record(): void {
		// add follow first
		$add = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$add->set_param( 'post_id', $this->event1 );
		$add->set_param( 'post_type', 'artpulse_event' );
		rest_get_server()->dispatch( $add );

		$req = new \WP_REST_Request( 'DELETE', '/artpulse/v1/follows' );
		$req->set_param( 'post_id', $this->event1 );
		$req->set_param( 'post_type', 'artpulse_event' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$this->assertEmpty( get_user_meta( $this->user1, '_ap_follows', true ) );
		$this->assertSame( 0, (int) get_user_meta( $this->user1, 'ap_following_count', true ) );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE user_id = %d AND object_id = %d",
				$this->user1,
				$this->event1
			)
		);
		$this->assertSame( 0, $count );
	}

	public function test_list_follows_returns_all_rows(): void {
		$req1 = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$req1->set_param( 'post_id', $this->event1 );
		$req1->set_param( 'post_type', 'artpulse_event' );
		rest_get_server()->dispatch( $req1 );

		$req2 = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$req2->set_param( 'post_id', $this->event2 );
		$req2->set_param( 'post_type', 'artpulse_event' );
		rest_get_server()->dispatch( $req2 );

		$list = new \WP_REST_Request( 'GET', '/artpulse/v1/follows' );
		$res  = rest_get_server()->dispatch( $list );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 2, $data );
		$ids = wp_list_pluck( $data, 'object_id' );
		$this->assertContains( $this->event1, $ids );
		$this->assertContains( $this->event2, $ids );
	}

	public function test_get_followers_returns_user_ids(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$req->set_param( 'post_id', $this->user2 );
		$req->set_param( 'post_type', 'user' );
		rest_get_server()->dispatch( $req );

		$followers = new \WP_REST_Request( 'GET', '/artpulse/v1/followers/' . $this->user2 );
		$res       = rest_get_server()->dispatch( $followers );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( $this->user2, $data['user_id'] );
		$this->assertContains( $this->user1, $data['followers'] );
		$this->assertSame( 1, (int) get_user_meta( $this->user2, 'ap_follower_count', true ) );
	}

	public function test_follow_user_post_and_delete(): void {
		$post = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$post->set_param( 'post_id', $this->user2 );
		$post->set_param( 'post_type', 'user' );
		$res = rest_get_server()->dispatch( $post );
		$this->assertSame( 200, $res->get_status() );

		$delete = new \WP_REST_Request( 'DELETE', '/artpulse/v1/follows' );
		$delete->set_param( 'post_id', $this->user2 );
		$delete->set_param( 'post_type', 'user' );
		$res = rest_get_server()->dispatch( $delete );
		$this->assertSame( 200, $res->get_status() );
	}

	public function test_follow_user_invalid_id_returns_404(): void {
		$invalid = new \WP_REST_Request( 'POST', '/artpulse/v1/follows' );
		$invalid->set_param( 'post_id', 999999 );
		$invalid->set_param( 'post_type', 'user' );
		$res = rest_get_server()->dispatch( $invalid );
		$this->assertSame( 404, $res->get_status() );

		$invalid = new \WP_REST_Request( 'DELETE', '/artpulse/v1/follows' );
		$invalid->set_param( 'post_id', 999999 );
		$invalid->set_param( 'post_type', 'user' );
		$res = rest_get_server()->dispatch( $invalid );
		$this->assertSame( 404, $res->get_status() );
	}

	public function test_missing_table_returns_error(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_follows';
		$wpdb->query( "DROP TABLE IF EXISTS $table" );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/follows' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 500, $res->get_status() );
		$this->assertInstanceOf( \WP_Error::class, $res->as_error() );
		$this->assertSame( 'missing_table', $res->as_error()->get_error_code() );
	}
}
