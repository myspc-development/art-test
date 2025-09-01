<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group REST
 */
class FollowFeedRouteTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create();
		require_once dirname( __DIR__, 2 ) . '/follow-api.php';
		do_action( 'rest_api_init' );
	}

	public function test_feed_requires_read_capability(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/follow/feed' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_feed_returns_array_for_logged_in_user(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/follow/feed' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertIsArray( $res->get_data() );
	}
}
