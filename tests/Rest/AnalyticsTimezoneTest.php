<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\EventAnalyticsController;
use ArtPulse\Rest\RsvpDbController;

/**
 * @group restapi
 */
class AnalyticsTimezoneTest extends \WP_UnitTestCase {
	private int $user;
	private int $event;
	private string $prevDate;
	private string $todayDate;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Core\Activator::activate();
		$this->user  = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->event = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->user,
			)
		);
		update_option( 'timezone_string', 'America/New_York' );
		$tz       = new \DateTimeZone( 'America/New_York' );
		$utc      = new \DateTimeZone( 'UTC' );
		$midnight = new \DateTime( 'now', $tz );
		$midnight->setTime( 0, 0 );
		$before          = ( clone $midnight )->modify( '-10 minutes' );
		$after           = ( clone $midnight )->modify( '+10 minutes' );
		$this->prevDate  = $before->format( 'Y-m-d' );
		$this->todayDate = $after->format( 'Y-m-d' );
		$time1           = ( clone $before )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
		$time2           = ( clone $after )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_rsvps';
		$wpdb->insert(
			$table,
			array(
				'event_id'   => $this->event,
				'user_id'    => 0,
				'name'       => 'A',
				'email'      => 'a@example.com',
				'status'     => 'going',
				'created_at' => $time1,
			)
		);
		$wpdb->insert(
			$table,
			array(
				'event_id'   => $this->event,
				'user_id'    => 0,
				'name'       => 'B',
				'email'      => 'b@example.com',
				'status'     => 'going',
				'created_at' => $time2,
			)
		);
		EventAnalyticsController::register();
		RsvpDbController::register();
		do_action( 'rest_api_init' );
	}

	public function test_timezone_bucket_and_range_validation(): void {
		wp_set_current_user( $this->user );
		$req = new \WP_REST_Request( 'GET', '/ap/v1/analytics/events/summary' );
		$req->set_param( 'range', '7d' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data  = $res->get_data();
		$dates = wp_list_pluck( $data['trend'], 'date' );
		$this->assertContains( $this->prevDate, $dates );
		$this->assertContains( $this->todayDate, $dates );

		$req = new \WP_REST_Request( 'GET', '/ap/v1/analytics/events/summary' );
		$req->set_param( 'start', '2020-01-02' );
		$req->set_param( 'end', '2020-01-01' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
		$this->assertSame( 'invalid_range', $res->get_data()['code'] );
	}
}
