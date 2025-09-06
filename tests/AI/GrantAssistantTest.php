<?php
namespace ArtPulse\AI;

if ( ! function_exists( __NAMESPACE__ . '\sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return $key; }
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $text ) {
		return $text; }
}
if ( ! function_exists( __NAMESPACE__ . '\esc_html' ) ) {
	function esc_html( $text ) {
		return $text; }
}
if ( ! function_exists( __NAMESPACE__ . '\wpautop' ) ) {
	function wpautop( $text ) {
		return '<p>' . $text . '</p>'; }
}
if ( ! function_exists( __NAMESPACE__ . '\rest_ensure_response' ) ) {
	function rest_ensure_response( $data ) {
		return $data; }
}
if ( ! function_exists( __NAMESPACE__ . '\is_user_logged_in' ) ) {
	function is_user_logged_in() {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\add_action' ) ) {
	function add_action( $h, $cb ) {}
}

use PHPUnit\Framework\TestCase;
use ArtPulse\Tests\Rest\RequestStub as TestRequest;

require_once __DIR__ . '/../Rest/RequestStub.php';

/**

 * @group AI
 */

class GrantAssistantTest extends TestCase {

	public function test_generate_returns_prompted_text(): void {
			$req = new TestRequest( 'POST', '/' );
			$req->set_param( 'type', 'project_summary' );
			$req->set_param( 'tone', 'grant' );
			$req->set_param( 'source', 'Community arts event.' );

			$res  = GrantAssistant::generate( $req );
			$data = $res->get_data();
			$this->assertStringContainsString( 'Community arts event.', $data['draft'] );
			$this->assertStringContainsString( '<p>', $data['output'] );
	}
}
