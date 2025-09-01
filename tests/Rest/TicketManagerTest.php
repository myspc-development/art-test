<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\TicketManager;


/**
 * @group REST
 */
class TicketManagerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $user_id;
	private array $emails = array();

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		add_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10, 6 );
		do_action( 'init' );
		$this->user_id = self::factory()->user->create( array( 'user_email' => 'buyer@test.com' ) );
		wp_set_current_user( $this->user_id );
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
			)
		);
		TicketManager::register();
		do_action( 'rest_api_init' );
	}

	public function tear_down() {
		remove_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10 );
		parent::tear_down();
	}

	public function capture_mail( $null, $to, $subject, $message ): bool {
		$this->emails[] = array( $to, $subject, $message );
		return true;
	}

	private function create_ticket_tier( int $inventory, int $sold = 0, int $max = 0 ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$wpdb->insert(
			$table,
			array(
				'event_id'     => $this->event_id,
				'name'         => 'General',
				'price'        => 0,
				'inventory'    => $inventory,
				'sold'         => $sold,
				'max_per_user' => $max,
			)
		);
		return $wpdb->insert_id;
	}

	public function test_inventory_check_blocks_over_purchase(): void {
		$ticket_id = $this->create_ticket_tier( 1, 0 );
		$req       = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/buy-ticket" );
		$req->set_param( 'ticket_id', $ticket_id );
		$req->set_param( 'quantity', 2 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 409, $res->get_status() );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_tickets';
		$sold  = $wpdb->get_var( $wpdb->prepare( "SELECT sold FROM $table WHERE id = %d", $ticket_id ) );
		$this->assertSame( '0', $sold );
		$ticket_table = $wpdb->prefix . 'ap_tickets';
		$count        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $ticket_table WHERE ticket_tier_id = %d", $ticket_id ) );
		$this->assertSame( '0', $count );
	}

	public function test_purchase_generates_code_and_increments_inventory(): void {
		$ticket_id = $this->create_ticket_tier( 5, 0 );
		$req       = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/buy-ticket" );
		$req->set_param( 'ticket_id', $ticket_id );
		$req->set_param( 'quantity', 1 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertArrayHasKey( 'ticket_code', $data );
		$this->assertNotEmpty( $data['ticket_code'] );
		global $wpdb;
		$ticket_table = $wpdb->prefix . 'ap_tickets';
		$code         = $wpdb->get_var( $wpdb->prepare( "SELECT code FROM $ticket_table WHERE ticket_tier_id = %d AND user_id = %d", $ticket_id, $this->user_id ) );
		$this->assertSame( $data['ticket_code'], $code );
		$event_table = $wpdb->prefix . 'ap_event_tickets';
		$sold        = $wpdb->get_var( $wpdb->prepare( "SELECT sold FROM $event_table WHERE id = %d", $ticket_id ) );
		$this->assertSame( '1', $sold );
	}

	public function test_email_sent_on_purchase(): void {
		$ticket_id = $this->create_ticket_tier( 2, 0 );
		$req       = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/buy-ticket" );
		$req->set_param( 'ticket_id', $ticket_id );
		$req->set_param( 'quantity', 1 );
		rest_get_server()->dispatch( $req );
		$this->assertCount( 1, $this->emails );
		$this->assertSame( 'buyer@test.com', $this->emails[0][0] );
	}

	public function test_per_user_limit_blocks_extra_purchase(): void {
		$ticket_id = $this->create_ticket_tier( 5, 0, 1 );
		$req       = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/buy-ticket" );
		$req->set_param( 'ticket_id', $ticket_id );
		$req->set_param( 'quantity', 1 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$req = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/buy-ticket" );
		$req->set_param( 'ticket_id', $ticket_id );
		$req->set_param( 'quantity', 1 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 409, $res->get_status() );
	}

	public function test_private_link_email_triggered_on_order_hook(): void {
		$tier_id = $this->create_ticket_tier( 1, 0 );
		update_post_meta( $this->event_id, '_ap_virtual_event_url', 'http://example.com' );
		update_post_meta( $this->event_id, '_ap_virtual_access_enabled', 1 );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_tickets';
		$wpdb->insert(
			$table,
			array(
				'user_id'        => $this->user_id,
				'event_id'       => $this->event_id,
				'ticket_tier_id' => $tier_id,
				'code'           => 'HOOKCODE',
				'purchase_date'  => current_time( 'mysql' ),
				'status'         => 'active',
			)
		);
		$ticket_id = $wpdb->insert_id;

		TicketManager::handle_completed_order( $this->user_id, $ticket_id, 1 );

		$this->assertCount( 1, $this->emails );
		$this->assertSame( 'buyer@test.com', $this->emails[0][0] );
	}
}
