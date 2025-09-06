<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\PostTypeRegistrar;
use ArtPulse\Rest\ArtistEventsController;

/**

 * @group INTEGRATION
 */

class ArtistEventsEndpointTest extends \WP_UnitTestCase {

	private int $user_id;
	private array $event_ids;
	/**
	 * Filter handle to remove after test.
	 *
	 * @var callable
	 */
	private $filter;

	public function set_up() {
		parent::set_up();
		PostTypeRegistrar::register();

		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );

		$this->event_ids = array(
			self::factory()->post->create(
				array(
					'post_type'   => 'artpulse_event',
					'post_status' => 'publish',
					'post_author' => $this->user_id,
				)
			),
			self::factory()->post->create(
				array(
					'post_type'   => 'artpulse_event',
					'post_status' => 'draft',
					'post_author' => $this->user_id,
				)
			),
		);

		$this->filter = function ( \WP_Query $q ) {
			if ( 'artpulse_event' === $q->get( 'post_type' ) ) {
				$q->set( 'post_status', array( 'publish' ) );
			}
		};
		add_action( 'pre_get_posts', $this->filter );

		ArtistEventsController::register();
		do_action( 'rest_api_init' );
	}

	public function tear_down() {
		remove_action( 'pre_get_posts', $this->filter );
		parent::tear_down();
	}

	public function test_returns_published_and_draft_for_author(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/artist-events' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$statuses = wp_list_pluck( $res->get_data(), 'status' );
		$this->assertContains( 'publish', $statuses );
		$this->assertContains( 'draft', $statuses );
		$this->assertCount( 2, $statuses );
	}
}
