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

/**

 * @group ai

 */

class DummyRequest {

	private array $p;
	public function __construct( array $p ) {
		$this->p = $p; }
	public function get_param( $k ) {
		return $this->p[ $k ] ?? null; }
}

class GrantAssistantTest extends TestCase {

	public function test_generate_returns_prompted_text(): void {
		$req = new DummyRequest(
			array(
				'type'   => 'project_summary',
				'tone'   => 'grant',
				'source' => 'Community arts event.',
			)
		);
		$res = GrantAssistant::generate( $req );
		$this->assertStringContainsString( 'Community arts event.', $res['draft'] );
		$this->assertStringContainsString( '<p>', $res['output'] );
	}
}
