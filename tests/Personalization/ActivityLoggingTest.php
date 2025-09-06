<?php
namespace ArtPulse\Personalization\Tests;

use ArtPulse\Personalization\RecommendationEngine;
use ArtPulse\Community\FavoritesManager;
use ArtPulse\Community\FollowManager;
use ArtPulse\Community\NotificationManager;
use ArtPulse\Rest\RsvpRestController;

/**

 * @group PERSONALIZATION
 */

class ActivityLoggingTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $followee_id;
	private int $event_id;

	public function set_up() {
		parent::set_up();
		RecommendationEngine::install_table();
		FavoritesManager::install_favorites_table();
		FollowManager::install_follows_table();
		NotificationManager::install_notifications_table();

		RsvpRestController::register();
		do_action( 'rest_api_init' );

		$this->user_id     = self::factory()->user->create();
		$this->followee_id = self::factory()->user->create();
		$this->event_id    = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->followee_id,
			)
		);
		update_post_meta( $this->event_id, 'event_rsvp_enabled', '1' );
		update_post_meta( $this->event_id, 'event_rsvp_limit', 0 );
		update_post_meta( $this->event_id, 'event_rsvp_list', array() );
		update_post_meta( $this->event_id, 'event_waitlist', array() );
	}

	public function test_add_favorite_logs_activity(): void {
		FavoritesManager::add_favorite( $this->user_id, $this->event_id, 'artpulse_event' );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_user_activity';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT object_id, object_type, action FROM $table WHERE user_id = %d",
				$this->user_id
			),
			ARRAY_A
		);

		$this->assertNotEmpty( $row );
		$this->assertSame( $this->event_id, (int) $row['object_id'] );
		$this->assertSame( 'artpulse_event', $row['object_type'] );
		$this->assertSame( 'favorite', $row['action'] );
	}

	public function test_rsvp_logs_activity(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/rsvp' );
		$req->set_param( 'event_id', $this->event_id );
		rest_get_server()->dispatch( $req );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_user_activity';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT object_id, object_type, action FROM $table WHERE user_id = %d",
				$this->user_id
			),
			ARRAY_A
		);

		$this->assertNotEmpty( $row );
		$this->assertSame( $this->event_id, (int) $row['object_id'] );
		$this->assertSame( 'event', $row['object_type'] );
		$this->assertSame( 'rsvp', $row['action'] );
	}

	public function test_add_follow_logs_activity(): void {
		FollowManager::add_follow( $this->user_id, $this->followee_id, 'user' );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_user_activity';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT object_id, object_type, action FROM $table WHERE user_id = %d",
				$this->user_id
			),
			ARRAY_A
		);

		$this->assertNotEmpty( $row );
		$this->assertSame( $this->followee_id, (int) $row['object_id'] );
		$this->assertSame( 'user', $row['object_type'] );
		$this->assertSame( 'follow', $row['action'] );
	}
}
