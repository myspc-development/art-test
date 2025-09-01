<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\PaymentWebhookController;


/**
 * @group REST
 */
class PaymentWebhookControllerTest extends \WP_UnitTestCase {

	private int $ticket_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		// ensure db tables
		do_action( 'init' );
		$this->user_id = self::factory()->user->create();
		global $wpdb;
		$table = $wpdb->prefix . 'ap_tickets';
		$wpdb->insert(
			$table,
			array(
				'user_id'        => $this->user_id,
				'event_id'       => 1,
				'ticket_tier_id' => 1,
				'code'           => 'CODE',
				'status'         => 'pending',
				'purchase_date'  => current_time( 'mysql' ),
			)
		);
		$this->ticket_id = $wpdb->insert_id;

		PaymentWebhookController::register();
		do_action( 'rest_api_init' );
	}

	public function test_successful_webhook_activates_ticket(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/webhook' );
		$req->set_param( 'provider', 'test' );
		$req->set_param( 'status', 'success' );
		$req->set_param( 'ticket_id', $this->ticket_id );
		$req->set_param( 'user_id', $this->user_id );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'ok', $res->get_data()['status'] );

		global $wpdb;
		$table  = $wpdb->prefix . 'ap_tickets';
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM $table WHERE id = %d", $this->ticket_id ) );
		$this->assertSame( 'active', $status );
	}

	public function test_missing_fields_return_ignored(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/webhook' );
		$req->set_param( 'status', 'success' );
		$req->set_param( 'ticket_id', $this->ticket_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( 'ignored' => true ), $res->get_data() );

		$req2 = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/webhook' );
		$req2->set_param( 'status', 'success' );
		$req2->set_param( 'user_id', $this->user_id );
		$res2 = rest_get_server()->dispatch( $req2 );
		$this->assertSame( 200, $res2->get_status() );
		$this->assertSame( array( 'ignored' => true ), $res2->get_data() );
	}
}
