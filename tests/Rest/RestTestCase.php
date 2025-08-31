<?php
namespace ArtPulse\Rest\Tests;

/**
 * Base class for REST API tests.
 *
 * @group restapi
 */
abstract class RestTestCase extends \WP_UnitTestCase {
	protected int $admin;
	protected int $member;
	protected int $artist;
	protected \Spy_REST_Server $server;

	public function set_up() {
		parent::set_up();

		if ( ! defined( 'AP_TEST_MODE' ) ) {
			define( 'AP_TEST_MODE', true );
		}

		if ( ! defined( 'ARTPULSE_API_NAMESPACE' ) ) {
			define( 'ARTPULSE_API_NAMESPACE', 'artpulse/v1' );
		}

		$this->admin  = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$this->member = self::factory()->user->create( [ 'role' => 'member' ] );
		$this->artist = self::factory()->user->create( [ 'role' => 'artist' ] );

		global $wp_rest_server;
		$wp_rest_server = new \Spy_REST_Server();
		do_action( 'rest_api_init' );
		$this->server = $wp_rest_server;

		wp_set_current_user( $this->admin );
	}

	public function tear_down() {
		wp_set_current_user( 0 );
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	protected function asAdmin(): void {
		wp_set_current_user( $this->admin );
	}

	protected function asMember(): void {
		wp_set_current_user( $this->member );
	}

	protected function asArtist(): void {
		wp_set_current_user( $this->artist );
	}

	protected function json( \WP_REST_Response $response ): array {
		return json_decode( wp_json_encode( $response->get_data() ), true );
	}

	protected function ok( \WP_REST_Response $response ): array {
		$this->assertSame( 200, $response->get_status() );
		return $this->json( $response );
	}

	protected function nonce(): string {
		return wp_create_nonce( 'wp_rest' );
	}
}
