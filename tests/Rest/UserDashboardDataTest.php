<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Community\FavoritesManager;

/**
 * @group REST
 */
class UserDashboardDataTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $event_id;
	private int $artist_id;
	private int $org_id;
	private int $artwork_id;

	public function set_up() {
		parent::set_up();
		FavoritesManager::install_favorites_table();

		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );
		update_user_meta( $this->user_id, 'user_badges', array( 'gold' ) );

		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Sample Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event_id, '_ap_event_date', date( 'Y-m-d', strtotime( '+1 day' ) ) );
		update_user_meta( $this->user_id, 'ap_rsvp_events', array( $this->event_id ) );
		FavoritesManager::add_favorite( $this->user_id, $this->event_id, 'artpulse_event' );

		$this->artist_id  = wp_insert_post(
			array(
				'post_title'  => 'Sample Artist',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);
		$this->org_id     = wp_insert_post(
			array(
				'post_title'  => 'Sample Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
			)
		);
		$this->artwork_id = wp_insert_post(
			array(
				'post_title'  => 'Sample Artwork',
				'post_type'   => 'artpulse_artwork',
				'post_status' => 'publish',
			)
		);
		FavoritesManager::add_favorite( $this->user_id, $this->artist_id, 'artpulse_artist' );
		FavoritesManager::add_favorite( $this->user_id, $this->org_id, 'artpulse_org' );
		FavoritesManager::add_favorite( $this->user_id, $this->artwork_id, 'artpulse_artwork' );
		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_dashboard_data_returns_badges(): void {
		$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/dashboard' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( array( 'gold' ), $data['user_badges'] );
		$this->assertSame( 4, $data['favorite_count'] );
		$this->assertSame( 1, $data['rsvp_count'] );
		$this->assertSame( 1, $data['my_event_count'] );
	}

	public function test_dashboard_data_includes_event_lists(): void {
		$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/dashboard' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertIsArray( $data['rsvp_events'] );
		$this->assertCount( 1, $data['rsvp_events'] );
		$this->assertSame( $this->event_id, $data['rsvp_events'][0]['id'] );
		$this->assertIsArray( $data['favorite_events'] );
		$this->assertCount( 1, $data['favorite_events'] );
		$this->assertSame( $this->event_id, $data['favorite_events'][0]['id'] );
		$this->assertIsArray( $data['favorite_artists'] );
		$this->assertCount( 1, $data['favorite_artists'] );
		$this->assertSame( $this->artist_id, $data['favorite_artists'][0]['id'] );
		$this->assertIsArray( $data['favorite_orgs'] );
		$this->assertCount( 1, $data['favorite_orgs'] );
		$this->assertSame( $this->org_id, $data['favorite_orgs'][0]['id'] );
		$this->assertIsArray( $data['favorite_artworks'] );
		$this->assertCount( 1, $data['favorite_artworks'] );
		$this->assertSame( $this->artwork_id, $data['favorite_artworks'][0]['id'] );
		$this->assertSame( 4, $data['favorite_count'] );
		$this->assertSame( 1, $data['rsvp_count'] );
		$this->assertSame( 1, $data['my_event_count'] );

		$this->assertIsArray( $data['my_events'] );
		$this->assertCount( 1, $data['my_events'] );
		$this->assertSame( $this->event_id, $data['my_events'][0]['id'] );
		$this->assertTrue( $data['my_events'][0]['rsvped'] );
		$this->assertTrue( $data['my_events'][0]['favorited'] );
		$this->assertIsArray( $data['next_event'] );
		$this->assertSame( $this->event_id, $data['next_event']['id'] );
	}

	public function test_dashboard_data_returns_theme(): void {
		update_user_meta( $this->user_id, 'ap_dashboard_theme', 'dark' );
		$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/dashboard' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertSame( 'dark', $data['dashboard_theme'] );
	}

	public function test_dashboard_lists_favorites_for_all_types(): void {
			$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/dashboard' );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 200, $response->get_status() );
			$data = $response->get_data();

		$map = array(
			'favorite_events'   => $this->event_id,
			'favorite_artists'  => $this->artist_id,
			'favorite_orgs'     => $this->org_id,
			'favorite_artworks' => $this->artwork_id,
		);

		foreach ( $map as $key => $id ) {
			$this->assertIsArray( $data[ $key ] );
			$this->assertCount( 1, $data[ $key ] );
			$first = $data[ $key ][0];
			$this->assertSame( $id, $first['id'] );
			$this->assertArrayHasKey( 'title', $first );
			$this->assertArrayHasKey( 'link', $first );
			if ( $key === 'favorite_events' ) {
				$this->assertArrayHasKey( 'date', $first );
			}
		}

			$this->assertSame( 4, $data['favorite_count'] );
	}

	public function test_dashboard_lists_current_users_draft_and_published_events_only(): void {
			$draft_id   = wp_insert_post(
				array(
					'post_title'  => 'Draft Event',
					'post_type'   => 'artpulse_event',
					'post_status' => 'draft',
					'post_author' => $this->user_id,
				)
			);
			$other_user = self::factory()->user->create();
			wp_insert_post(
				array(
					'post_title'  => 'Other Event',
					'post_type'   => 'artpulse_event',
					'post_status' => 'publish',
					'post_author' => $other_user,
				)
			);

			$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/dashboard' );
			$response = rest_get_server()->dispatch( $request );
			$this->assertSame( 200, $response->get_status() );
			$data      = $response->get_data();
			$event_ids = wp_list_pluck( $data['events'], 'id' );
			sort( $event_ids );
			$expected = array( $this->event_id, $draft_id );
			sort( $expected );
			$this->assertSame( $expected, $event_ids );
	}
}
