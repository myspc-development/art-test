<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Server;
use ArtPulse\Rest\UserAccountRestController;

/**
 * @group REST
 */
class UserAccountRestControllerTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $post_id;

	public function set_up() {
		parent::set_up();

		$this->user_id = self::factory()->user->create(
			array(
				'user_email'   => 'tester@example.com',
				'display_name' => 'Tester',
			)
		);

		wp_set_current_user( $this->user_id );

		update_user_meta( $this->user_id, 'ap_membership_level', 'Free' );
		update_user_meta( $this->user_id, 'ap_membership_expires', strtotime( '2030-01-01' ) );
		update_user_meta( $this->user_id, 'ap_country', 'US' );
		update_user_meta( $this->user_id, 'ap_state', 'CA' );
		update_user_meta( $this->user_id, 'ap_city', 'LA' );

		$this->post_id = wp_insert_post(
			array(
				'post_title'  => 'Sample Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
			)
		);

		UserAccountRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_export_route_returns_profile_and_posts(): void {
		$request  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/export' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'profile', $data );
		$this->assertArrayHasKey( 'posts', $data );
		$this->assertSame( $this->user_id, $data['profile']['ID'] );
		$this->assertCount( 1, $data['posts'] );
		$this->assertSame( $this->post_id, $data['posts'][0]['ID'] );
	}

	public function test_export_route_as_csv_returns_csv_data(): void {
		$request = new \WP_REST_Request( 'GET', '/artpulse/v1/user/export' );
		$request->set_param( 'format', 'csv' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$headers = $response->get_headers();
		$this->assertSame( 'text/csv', $headers['Content-Type'] ?? $headers['content-type'] ?? null );

		$csv = $response->get_data();
		$this->assertIsString( $csv );
		$this->assertStringContainsString( 'Tester', $csv );
		$this->assertStringContainsString( 'Sample Event', $csv );
	}

	public function test_delete_route_trashes_posts_and_meta(): void {
		$request  = new \WP_REST_Request( 'POST', '/artpulse/v1/user/delete' );
		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( 'success' => true ), $response->get_data() );

		$this->assertSame( 'trash', get_post_status( $this->post_id ) );

		$keys = array(
			'ap_country',
			'ap_state',
			'ap_city',
			'ap_membership_level',
			'ap_membership_expires',
			'ap_membership_paused',
			'stripe_customer_id',
			'stripe_payment_ids',
			'ap_push_token',
			'ap_phone_number',
			'ap_sms_opt_in',
		);
		foreach ( $keys as $key ) {
			$this->assertEmpty( get_user_meta( $this->user_id, $key, true ) );
		}

		$this->assertFalse( get_userdata( $this->user_id ) );
	}
}
