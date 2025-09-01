<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\FavoritesManager;
use ArtPulse\Community\NotificationManager;
use ArtPulse\Core\CompetitionEntryManager;
use WP_UnitTestCase;

/**

 * @group community

 */

class FavoritesManagerExtendedTest extends WP_UnitTestCase {

	private int $user_id;
	private int $owner_id;

	public function set_up() {
		parent::set_up();
		FavoritesManager::install_favorites_table();
		NotificationManager::install_notifications_table();
		CompetitionEntryManager::install_table();

		$this->owner_id = self::factory()->user->create();
		$this->user_id  = self::factory()->user->create();
	}

	public function test_forum_and_competition_favorites(): void {
		$thread_id = wp_insert_post(
			array(
				'post_title'  => 'Thread',
				'post_type'   => 'ap_forum_thread',
				'post_status' => 'publish',
				'post_author' => $this->owner_id,
			)
		);

		$comp_id = wp_insert_post(
			array(
				'post_title'  => 'Competition',
				'post_type'   => 'ap_competition',
				'post_status' => 'publish',
			)
		);
		$art_id  = wp_insert_post(
			array(
				'post_title'  => 'Artwork',
				'post_type'   => 'artpulse_artwork',
				'post_status' => 'publish',
				'post_author' => $this->owner_id,
			)
		);
		CompetitionEntryManager::add_entry( $comp_id, $art_id, $this->owner_id );
		global $wpdb;
		$entry_id = (int) $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}ap_competition_entries LIMIT 1" );

		FavoritesManager::add_favorite( $this->user_id, $thread_id, 'ap_forum_thread' );
		FavoritesManager::add_favorite( $this->user_id, $entry_id, 'ap_competition_entry' );

		$fav = FavoritesManager::get_favorites( $this->user_id );
		$this->assertContains( $thread_id, $fav['forum'] );
		$this->assertContains( $entry_id, $fav['competition_entry'] );
	}
}
