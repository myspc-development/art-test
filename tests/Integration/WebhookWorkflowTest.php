<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\WebhookManager;
use WP_Error;

/**

 * @group INTEGRATION
 */

class WebhookWorkflowTest extends \WP_UnitTestCase {

	private array $requests = array();
	private $response;
	private int $org_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Integration\WebhookManager::maybe_install_tables();
		$this->response = array(
			'headers'  => array(),
			'body'     => '',
			'response' => array( 'code' => 200 ),
		);
		do_action( 'init' );
		WebhookManager::register();
		do_action( 'rest_api_init' );
		add_filter( 'pre_http_request', array( $this, 'intercept' ), 10, 3 );

		$this->org_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
			)
		);
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'intercept' ), 10 );

		// Clean up any webhook data to avoid leaking state between tests.
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_webhooks';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists === $table ) {
			$wpdb->query( "DELETE FROM $table" );
			$wpdb->query( "DROP TABLE IF EXISTS $table" );
		}

		parent::tear_down();
	}

	public function intercept( $pre, $args, $url ) {
		$this->requests[] = array( $url, $args );
		return $this->response instanceof WP_Error ? $this->response : $this->response;
	}

	public function test_register_webhook_and_trigger_event_sends_payload(): void {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/org/' . $this->org_id . '/webhooks' );
		$req->set_param( 'url', 'http://example.com/hook' );
		$req->set_param( 'events', array( 'ticket_sold' ) );
		$req->set_param( 'active', 1 );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'secret', $data );
		$hook_id = $data['id'];
		$secret  = $data['secret'];

		WebhookManager::trigger_event( 'ticket_sold', $this->org_id, array( 'ticket_id' => 5 ) );

		$this->assertCount( 1, $this->requests );
		[$url, $args] = $this->requests[0];
		$this->assertSame( 'http://example.com/hook', $url );
		$payload = json_decode( $args['body'], true );
		$this->assertSame( 'ticket_sold', $payload['event'] );
		$this->assertSame( 5, $payload['data']['ticket_id'] );
		$sig = 'sha256=' . hash_hmac( 'sha256', $args['body'], $secret );
		$this->assertSame( $sig, $args['headers']['X-ArtPulse-Signature'] );
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_webhooks';
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT last_status FROM $table WHERE id = %d", $hook_id ) );
		$this->assertSame( '200', $status );
	}

	public function test_failed_delivery_updates_status(): void {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/org/' . $this->org_id . '/webhooks' );
		$req->set_param( 'url', 'http://example.com/hook' );
		$req->set_param( 'events', array( 'ticket_sold' ) );
		$req->set_param( 'active', 1 );
		$res     = rest_get_server()->dispatch( $req );
		$data    = $res->get_data();
		$hook_id = $data['id'];

		$this->response = new WP_Error( 'fail', 'network' );
		WebhookManager::trigger_event( 'ticket_sold', $this->org_id, array( 'ticket_id' => 9 ) );

		$this->assertCount( 3, $this->requests );
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_webhooks';
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT last_status FROM $table WHERE id = %d", $hook_id ) );
		$this->assertSame( 'error', $status );
	}
}
