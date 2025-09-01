<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\CompetitionEntryManager;

/**

 * @group CORE

 */

class CompetitionEntryManagerTest extends WP_UnitTestCase {

	private int $comp_id;
	private int $artwork_id;

	public function set_up() {
		parent::set_up();
		CompetitionEntryManager::install_table();
		$this->comp_id    = wp_insert_post(
			array(
				'post_title'  => 'Competition',
				'post_type'   => 'ap_competition',
				'post_status' => 'publish',
			)
		);
		$this->artwork_id = wp_insert_post(
			array(
				'post_title'  => 'Artwork',
				'post_type'   => 'artpulse_artwork',
				'post_status' => 'publish',
			)
		);
	}

	public function test_add_and_vote_entry(): void {
		CompetitionEntryManager::add_entry( $this->comp_id, $this->artwork_id, 1 );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_competition_entries';
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE competition_id = %d AND artwork_id = %d",
				$this->comp_id,
				$this->artwork_id
			)
		);
		$this->assertSame( 1, $count );

		$votes = CompetitionEntryManager::vote( 1, 2 );
		$this->assertSame( 1, $votes );
	}
}
