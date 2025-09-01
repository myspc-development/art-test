<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Core\UserEngagementLogger;
use ArtPulse\Community\FollowManager;
use ArtPulse\Community\FavoritesManager;

/**
 * @group REST
 */
class UserEngagementFeedTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $event_id;
	private int $follow_id;

	public function set_up() {
		parent::set_up();
		UserEngagementLogger::install_table();
		FollowManager::install_follows_table();
		FavoritesManager::install_favorites_table();

		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );

		$this->event_id  = wp_insert_post(
			array(
				'post_title'  => 'Sample Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		$this->follow_id = self::factory()->user->create();

		UserEngagementLogger::log( $this->user_id, 'rsvp', $this->event_id );
		UserEngagementLogger::log( $this->user_id, 'favorite', $this->event_id );
		UserEngagementLogger::log( $this->user_id, 'follow', $this->follow_id );

		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_feed_returns_recent_items(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/user/engagement' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 3, $data );
		$types = wp_list_pluck( $data, 'type' );
		$this->assertContains( 'rsvp', $types );
		$this->assertContains( 'favorite', $types );
		$this->assertContains( 'follow', $types );
	}
}
