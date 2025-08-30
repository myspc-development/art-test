<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\AnalyticsRestController;
use ArtPulse\Core\EventMetrics;

/**
 * @group restapi
 */
class AnalyticsRestControllerTest extends \WP_UnitTestCase {

        private int $event_id;
        private string $ticket_table;

        public function set_up() {
                parent::set_up();
                EventMetrics::install_table();
                \ArtPulse\Monetization\TicketManager::install_purchases_table();

                $this->event_id = self::factory()->post->create(
                        array(
                                'post_type'   => 'artpulse_event',
                                'post_status' => 'publish',
                        )
                );

               wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

                global $wpdb;
                $this->ticket_table = $wpdb->prefix . 'ap_tickets';

                AnalyticsRestController::register();
                do_action( 'rest_api_init' );
        }

	public function test_trends_and_export(): void {
		global $wpdb;
		$metric_table = $wpdb->prefix . 'ap_event_metrics';
		$today        = date( 'Y-m-d' );
		$yesterday    = date( 'Y-m-d', strtotime( '-1 day' ) );
		$wpdb->insert(
			$metric_table,
			array(
				'event_id' => $this->event_id,
				'metric'   => 'view',
				'day'      => $today,
				'count'    => 5,
			)
		);
		$wpdb->insert(
			$metric_table,
			array(
				'event_id' => $this->event_id,
				'metric'   => 'favorite',
				'day'      => $today,
				'count'    => 2,
			)
		);
		$wpdb->insert(
			$metric_table,
			array(
				'event_id' => $this->event_id,
				'metric'   => 'view',
				'day'      => $yesterday,
				'count'    => 3,
			)
		);

		$wpdb->insert(
			$this->ticket_table,
			array(
				'user_id'        => 1,
				'event_id'       => $this->event_id,
				'ticket_tier_id' => 1,
				'code'           => 'A',
				'purchase_date'  => $today,
				'status'         => 'active',
			)
		);

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/analytics/trends' );
		$req->set_param( 'event_id', $this->event_id );
		$req->set_param( 'days', 2 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 2, $data['days'] );
		$this->assertSame( array( 3, 5 ), $data['views'] );
		$this->assertSame( 2, $data['favorites'][1] );
		$this->assertSame( 1, $data['tickets'][1] );

		$req2 = new \WP_REST_Request( 'GET', '/artpulse/v1/analytics/export' );
		$req2->set_param( 'event_id', $this->event_id );
		$req2->set_param( 'days', 2 );
		$res2 = rest_get_server()->dispatch( $req2 );
		$this->assertSame( 200, $res2->get_status() );
		$csv = $res2->get_data();
		$this->assertStringContainsString( 'day,views,favorites,tickets', $csv );
	}
}
