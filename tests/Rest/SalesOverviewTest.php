<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\SalesOverview;


/**
 * @group REST
 */
class SalesOverviewTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $tier_id;
	private int $artist_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		do_action( 'init' );
		$this->artist_id = self::factory()->user->create();
		$this->event_id  = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->artist_id,
			)
		);
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->insert(
			$table,
			array(
				'event_id'  => $this->event_id,
				'name'      => 'General',
				'price'     => 10,
				'inventory' => 0,
			)
		);
		$this->tier_id = $wpdb->insert_id;
		$table         = $wpdb->prefix . 'ap_tickets';
		$wpdb->insert(
			$table,
			array(
				'user_id'        => self::factory()->user->create(),
				'event_id'       => $this->event_id,
				'ticket_tier_id' => $this->tier_id,
				'code'           => 'AAA',
				'status'         => 'active',
				'purchase_date'  => current_time( 'mysql' ),
			)
		);
		SalesOverview::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->artist_id );
	}

	public function test_get_sales_returns_totals(): void {
		$req  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/sales' );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 1, $data['tickets_sold'] );
		$this->assertSame( 10.0, $data['total_revenue'] );
		$this->assertCount( 1, $data['trend'] );
	}
}
