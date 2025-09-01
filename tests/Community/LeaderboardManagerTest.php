<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\FavoritesManager;
use ArtPulse\Community\LeaderboardManager;
use ArtPulse\Core\UserEngagementLogger;
use WP_UnitTestCase;

/**

 * @group COMMUNITY

 */

class LeaderboardManagerTest extends WP_UnitTestCase {

	private int $user1;
	private int $user2;
	private int $owner;
	private int $post_id;

	public function set_up() {
		parent::set_up();
		FavoritesManager::install_favorites_table();
		UserEngagementLogger::install_table();

		$this->owner = self::factory()->user->create();
		$this->user1 = self::factory()->user->create();
		$this->user2 = self::factory()->user->create();

		$this->post_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->owner,
			)
		);
	}

	public function test_leaderboards(): void {
		FavoritesManager::add_favorite( $this->user1, $this->post_id, 'artpulse_event' );
		FavoritesManager::add_favorite( $this->user2, $this->post_id, 'artpulse_event' );
		FavoritesManager::add_favorite( $this->user1, $this->post_id, 'artpulse_event' );

		$helpful = LeaderboardManager::get_most_helpful( 2 );
		$this->assertSame( $this->user1, $helpful[0]['user_id'] );
		$this->assertSame( 2, $helpful[0]['favorites_given'] );

		$upvoted = LeaderboardManager::get_most_upvoted( 1 );
		$this->assertSame( $this->post_id, $upvoted[0]['object_id'] );
		$this->assertSame( 3, $upvoted[0]['favorites'] );
	}
}
