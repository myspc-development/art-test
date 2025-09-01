<?php
namespace ArtPulse\AI\Tests;

use ArtPulse\AI\OpenAIClient;
use WP_Error;
use WP_UnitTestCase;

/**

 * @group ai

 */

class OpenAIClientTest extends WP_UnitTestCase {
	private $http_response;
	private $last_request;

	public function set_up(): void {
		parent::set_up();
		$this->http_response = null;
		$this->last_request  = null;
		add_filter( 'pre_http_request', array( $this, 'mock_http' ), 10, 3 );
	}

	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_http' ), 10 );
		parent::tear_down();
	}

	public function mock_http( $pre, $args, $url ) {
		$this->last_request = array(
			'url'  => $url,
			'args' => $args,
		);
		return $this->http_response;
	}

	public function test_generateTags_and_generateSummary_success(): void {
		update_option( 'openai_api_key', 'abc123' );

		$this->http_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'alpha, beta' ) ) ) ) ),
		);
		$tags                = OpenAIClient::generateTags( 'text' );
		$this->assertSame( array( 'alpha', 'beta' ), $tags );

		$this->http_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'short summary' ) ) ) ) ),
		);
		$summary             = OpenAIClient::generateSummary( 'bio' );
		$this->assertSame( 'short summary', $summary );
	}

	public function test_generateTags_surfaces_wp_error_on_network_failure(): void {
		update_option( 'openai_api_key', 'abc123' );
		$this->http_response = new WP_Error( 'http_request_failed', 'Network down' );

		$res = OpenAIClient::generateTags( 'text' );
		$this->assertInstanceOf( WP_Error::class, $res );
		$this->assertSame( 'openai_request_failed', $res->get_error_code() );
	}

	public function test_generateSummary_handles_rate_limit_and_http_error(): void {
		update_option( 'openai_api_key', 'abc123' );

		$this->http_response = array(
			'response' => array( 'code' => 429 ),
			'body'     => '',
		);
		$res                 = OpenAIClient::generateSummary( 'bio' );
		$this->assertInstanceOf( WP_Error::class, $res );
		$this->assertSame( 'openai_rate_limited', $res->get_error_code() );

		$this->http_response = array(
			'response' => array( 'code' => 400 ),
			'body'     => '',
		);
		$res                 = OpenAIClient::generateSummary( 'bio' );
		$this->assertInstanceOf( WP_Error::class, $res );
		$this->assertSame( 'openai_http_error', $res->get_error_code() );
	}

	public function test_api_key_falls_back_to_constant(): void {
		delete_option( 'openai_api_key' );
		if ( ! defined( 'OPENAI_API_KEY' ) ) {
			define( 'OPENAI_API_KEY', 'const-key' );
		}
		$this->http_response = array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( array( 'choices' => array( array( 'message' => array( 'content' => 'ok' ) ) ) ) ),
		);
		OpenAIClient::generateSummary( 'bio' );
		$this->assertSame( 'Bearer const-key', $this->last_request['args']['headers']['Authorization'] );
	}
}
