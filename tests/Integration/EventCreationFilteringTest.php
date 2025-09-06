<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Rest\SubmissionRestController;
use ArtPulse\Frontend\EventFilter;

/**

 * @group INTEGRATION
 */

class EventCreationFilteringTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );

		SubmissionRestController::register();
		EventFilter::register();
		do_action( 'init' );
		do_action( 'rest_api_init' );
	}

	public function test_event_creation_and_filtering(): void {
		$nonce = wp_create_nonce( 'wp_rest' );
		$req   = new \WP_REST_Request( 'POST', '/artpulse/v1/submissions' );
		$req->add_header( 'X-WP-Nonce', $nonce );
		$req->set_body_params(
			array(
				'post_type'      => 'artpulse_event',
				'title'          => 'Filter Event',
				'event_date'     => '2030-01-01',
				'event_location' => 'Test',
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$id = $res->get_data()['id'];
		$this->assertSame( 'artpulse_event', get_post_type( $id ) );
		$this->assertSame( '2030-01-01', get_post_meta( $id, '_ap_event_date', true ) );

		// Filter helper
		$_REQUEST = array(
			'keyword'     => 'Filter Event',
			'_ajax_nonce' => wp_create_nonce( 'ap_event_filter_nonce' ),
		);
		ob_start();
		try {
			\ap_filter_events_callback();
		} catch ( \WPDieException $e ) {
			// expected
		}
		$html = ob_get_clean();
		$this->assertStringContainsString( 'Filter Event', $html );
	}
}
