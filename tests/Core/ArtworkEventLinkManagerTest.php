<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\ArtworkEventLinkManager;

/**

 * @group CORE

 */

class ArtworkEventLinkManagerTest extends WP_UnitTestCase {

	private int $artwork_id;
	private int $event_id;

	public function set_up() {
		parent::set_up();
		ArtworkEventLinkManager::install_table();
		$this->artwork_id = wp_insert_post(
			array(
				'post_title'  => 'Artwork',
				'post_type'   => 'artpulse_artwork',
				'post_status' => 'publish',
			)
		);
		$this->event_id   = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
	}

	public function test_link_and_unlink_records(): void {
		ArtworkEventLinkManager::link( $this->artwork_id, $this->event_id );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_artwork_event_links';
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE artwork_id = %d AND event_id = %d",
				$this->artwork_id,
				$this->event_id
			)
		);
		$this->assertSame( 1, $count );

		ArtworkEventLinkManager::unlink( $this->artwork_id, $this->event_id );
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE artwork_id = %d AND event_id = %d",
				$this->artwork_id,
				$this->event_id
			)
		);
		$this->assertSame( 0, $count );
	}
}
