<?php
namespace ArtPulse\Community\Tests;

use ArtPulse\Community\NotificationManager;
use ArtPulse\Tests\Email;
use WP_UnitTestCase;

/**

 * @group COMMUNITY
 */

class EmailDeliveryTest extends WP_UnitTestCase {

	private int $user_id;
	private array $requests = array();
	private array $from     = array();
	private array $names    = array();

	public static function setUpBeforeClass(): void {
			parent::setUpBeforeClass();
			Email::install();
	}

	public function set_up() {
			parent::set_up();
			NotificationManager::install_notifications_table();
			$this->user_id = self::factory()->user->create(
				array(
					'user_email'   => 'user@test.com',
					'display_name' => 'User',
				)
			);
			add_filter( 'pre_http_request', array( $this, 'capture_request' ), 10, 3 );
			add_filter( 'wp_mail_from', array( $this, 'capture_from' ), 20 );
			add_filter( 'wp_mail_from_name', array( $this, 'capture_name' ), 20 );
	}

	public function tear_down() {
			remove_filter( 'pre_http_request', array( $this, 'capture_request' ), 10 );
			remove_filter( 'wp_mail_from', array( $this, 'capture_from' ), 20 );
			remove_filter( 'wp_mail_from_name', array( $this, 'capture_name' ), 20 );
			Email::clear();
			parent::tear_down();
	}

	public function capture_request( $pre, $args, $url ) {
		$this->requests[] = array( $url, $args );
		return array(
			'headers'  => array(),
			'body'     => '',
			'response' => array( 'code' => 200 ),
		);
	}

	public function capture_from( $from ) {
		$this->from[] = $from;
		return $from;
	}

	public function capture_name( $name ) {
		$this->names[] = $name;
		return $name;
	}

	public function test_wp_mail_method_used(): void {
		update_option(
			'artpulse_settings',
			array(
				'email_method'       => 'wp_mail',
				'email_from_name'    => 'Admin',
				'email_from_address' => 'admin@test.com',
			)
		);
				NotificationManager::add( $this->user_id, 'comment', null, null, 'Hi' );
				$this->assertCount( 1, Email::messages() );
				$this->assertSame( 'admin@test.com', end( $this->from ) );
				$this->assertSame( 'Admin', end( $this->names ) );
				$this->assertEmpty( $this->requests );
	}

	public function test_mailgun_method_sends_request(): void {
		update_option(
			'artpulse_settings',
			array(
				'email_method'       => 'mailgun',
				'mailgun_api_key'    => 'key',
				'mailgun_domain'     => 'mg.test.com',
				'email_from_name'    => 'Admin',
				'email_from_address' => 'admin@test.com',
			)
		);
		NotificationManager::add( $this->user_id, 'comment', null, null, 'Hi' );
		$this->assertCount( 1, $this->requests );
		$this->assertStringContainsString( 'mg.test.com/messages', $this->requests[0][0] );
	}

	public function test_sendgrid_method_sends_request(): void {
		update_option(
			'artpulse_settings',
			array(
				'email_method'       => 'sendgrid',
				'sendgrid_api_key'   => 'key',
				'email_from_name'    => 'Admin',
				'email_from_address' => 'admin@test.com',
			)
		);
		NotificationManager::add( $this->user_id, 'comment', null, null, 'Hi' );
		$this->assertCount( 1, $this->requests );
		$this->assertStringContainsString( 'sendgrid', $this->requests[0][0] );
	}
}
