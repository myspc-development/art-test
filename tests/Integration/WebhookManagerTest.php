<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\WebhookManager;

/**

 * @group integration

 */

class WebhookManagerTest extends \WP_UnitTestCase {

	private array $requests = array();
	private int $org_id;
	private int $hook_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Integration\WebhookManager::maybe_install_tables();
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
		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhooks';
		$wpdb->insert(
			$table,
			array(
				'org_id' => $this->org_id,
				'url'    => 'http://example.com/hook',
				'events' => 'ticket_sold',
				'secret' => 'abc123',
				'active' => 1,
			)
		);
		$this->hook_id = $wpdb->insert_id;
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'intercept' ), 10 );
		parent::tear_down();
	}

	public function intercept( $pre, $args, $url ) {
		$this->requests[] = array( $url, $args );
		return array(
			'headers'  => array(),
			'body'     => '',
			'response' => array( 'code' => 200 ),
		);
	}

	public function test_webhook_signature_and_status(): void {
		WebhookManager::trigger_event( 'ticket_sold', $this->org_id, array( 'ticket_id' => 1 ) );
		$this->assertCount( 1, $this->requests );
		[$url, $args] = $this->requests[0];
		$this->assertSame( 'http://example.com/hook', $url );
		$body = $args['body'];
		$sig  = 'sha256=' . hash_hmac( 'sha256', $body, 'abc123' );
		$this->assertSame( $sig, $args['headers']['X-ArtPulse-Signature'] );
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_webhooks';
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT last_status FROM $table WHERE id = %d", $this->hook_id ) );
		$this->assertSame( '200', $status );
	}
}
