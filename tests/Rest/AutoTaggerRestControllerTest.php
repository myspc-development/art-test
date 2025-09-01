<?php
namespace ArtPulse\AI\Tests;

use ArtPulse\AI\AutoTaggerRestController;
use function add_filter;

/**
 * @group REST
 */
class AutoTaggerRestControllerTest extends \WP_UnitTestCase {

	private int $admin;
	private int $subscriber;
	private string $mock_body   = '';
	private array $request_args = array();

	public function set_up() {
		parent::set_up();
		$this->admin      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		AutoTaggerRestController::register();
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
	 * Ensure the /tag endpoint returns an array of tags for POST requests.
	 */
	public function test_endpoint_returns_tags(): void {
		wp_set_current_user( $this->admin );
		$this->mock_body = json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'abstract, modern' ) ) ) ) );
		$req             = new \WP_REST_Request( 'POST', '/artpulse/v1/tag' );
		$req->set_param( 'text', 'art' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( array( 'tags' => array( 'abstract', 'modern' ) ), $res->get_data() );
	}

	/**
	 * Input is sanitized and empty values return an error.
	 */
	public function test_invalid_text_returns_error(): void {
		wp_set_current_user( $this->admin );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/tag' );
		$req->set_param( 'text', '   ' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 400, $res->get_status() );
	}

	/**
	 * Subscribers without edit_posts capability cannot access the endpoint.
	 */
	public function test_requires_edit_posts_capability(): void {
		wp_set_current_user( $this->subscriber );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/tag' );
		$req->set_param( 'text', 'art' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	/**
	 * Custom prompts should be included in the OpenAI request body.
	 */
	public function test_custom_prompt_applied(): void {
		wp_set_current_user( $this->admin );
		update_option( 'artpulse_tag_prompt', 'Tag this:' );
		$this->mock_body = json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'a,b' ) ) ) ) );
		$req             = new \WP_REST_Request( 'POST', '/artpulse/v1/tag' );
		$req->set_param( 'text', 'painting' );
		rest_get_server()->dispatch( $req );
		$body = json_decode( $this->request_args['body'], true );
		$this->assertStringStartsWith( 'Tag this:', $body['messages'][1]['content'] );
	}
}
