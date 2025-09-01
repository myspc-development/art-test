<?php
namespace ArtPulse\AI\Tests;

use ArtPulse\AI\BioSummaryRestController;

/**
 * @group REST
 */
class BioSummaryRestControllerTest extends \WP_UnitTestCase {

	private int $admin;
	private int $subscriber;
	private string $mock_body   = '';
	private array $request_args = array();

	public function set_up() {
		parent::set_up();
		$this->admin      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		BioSummaryRestController::register();
		do_action( 'rest_api_init' );

		update_option( 'openai_api_key', 'test' );
		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'mock_request' ), 10 );
		parent::tear_down();
	}

	public function mock_request( $pre, $args, $url ) {
		if ( str_contains( $url, 'api.openai.com' ) ) {
			$this->request_args = $args;
			return array(
				'headers'  => array(),
				'response' => array( 'code' => 200 ),
				'body'     => $this->mock_body,
			);
		}
		return false;
	}

	/**
	 * Ensure the /bio-summary endpoint returns a summary string.
	 */
	public function test_endpoint_returns_summary(): void {
		wp_set_current_user( $this->admin );
		$this->mock_body = json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'A visionary artist blending tradition and technology.' ) ) ) ) );
		$req             = new \WP_REST_Request( 'POST', '/artpulse/v1/bio-summary' );
		$req->set_param( 'bio', 'Artist biography' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( 'summary' => 'A visionary artist blending tradition and technology.' ), $res->get_data() );
	}

	/**
	 * Invalid bio after sanitization should return an error.
	 */
	public function test_invalid_bio_returns_error(): void {
		wp_set_current_user( $this->admin );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/bio-summary' );
		$req->set_param( 'bio', '   ' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	/**
	 * Only users with edit_posts capability can access the endpoint.
	 */
	public function test_requires_edit_posts_capability(): void {
		wp_set_current_user( $this->subscriber );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/bio-summary' );
		$req->set_param( 'bio', 'Artist bio' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	/**
	 * Custom summary prompt should appear in the request body.
	 */
	public function test_custom_prompt_applied(): void {
		wp_set_current_user( $this->admin );
		update_option( 'artpulse_summary_prompt', 'Please summarize:' );
		$this->mock_body = json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'summary' ) ) ) ) );
		$req             = new \WP_REST_Request( 'POST', '/artpulse/v1/bio-summary' );
		$req->set_param( 'bio', 'My life' );
		rest_get_server()->dispatch( $req );
		$body = json_decode( $this->request_args['body'], true );
		$this->assertStringStartsWith( 'Please summarize:', $body['messages'][1]['content'] );
	}
}
