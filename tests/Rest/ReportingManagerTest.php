<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Admin\ReportingManager;
use ArtPulse\Monetization\TicketManager;


/**
 * @group REST
 */
class ReportingManagerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $ticket_tier;
	private int $ticket;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		do_action( 'init' );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
			)
		);
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->insert(
			$table,
			array(
				'event_id'  => $this->event_id,
				'name'      => 'General',
				'price'     => 0,
				'inventory' => 0,
			)
		);
		$this->ticket_tier = $wpdb->insert_id;
		$table             = $wpdb->prefix . 'ap_tickets';
		$wpdb->insert(
			$table,
			array(
				'user_id'        => 1,
				'event_id'       => $this->event_id,
				'ticket_tier_id' => $this->ticket_tier,
				'code'           => 'ABC',
				'status'         => 'active',
				'purchase_date'  => current_time( 'mysql' ),
			)
		);
		$this->ticket = $wpdb->insert_id;
		ReportingManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_export_returns_csv(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/admin/export' );
		$req->set_param( 'type', 'attendance' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertStringContainsString( 'ticket_id', $res->get_data() );
	}
}
