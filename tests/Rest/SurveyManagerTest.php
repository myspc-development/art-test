<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Admin\SurveyManager;


/**
 * @group REST
 */
class SurveyManagerTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $event_id;

	public function set_up() {
		parent::set_up();
		$this->user_id  = self::factory()->user->create();
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
			)
		);
		wp_set_current_user( $this->user_id );
		SurveyManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_post_records_response(): void {
		$req = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/survey" );
		$req->set_param( 'answers', array( 'q1' => 'a1' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$responses = get_post_meta( $this->event_id, 'ap_survey_responses', true );
		$this->assertCount( 1, $responses );
	}
}
