<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/../TestHelpers/FrontendFunctionStubs.php';
if ( ! function_exists( 'ArtPulse\\Frontend\\get_post_type' ) ) {
	function get_post_type( $id ) {
		return 'artpulse_event'; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\wp_update_post' ) ) {
	function wp_update_post( $arr ) {
		\ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$updated = $arr; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\get_posts' ) ) {
	function get_posts( $args = array() ) {
		\ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$passed_args = $args;
		return \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$posts; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\wp_set_post_terms' ) ) {
	function wp_set_post_terms( $id, $terms, $tax ) {
		\ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$terms = array( $id, $terms, $tax ); }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\wp_send_json_success' ) ) {
	function wp_send_json_success( $data ) {
		\ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$json = $data; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\wp_send_json_error' ) ) {
	function wp_send_json_error( $data ) {
		\ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$json_error = $data; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\media_handle_upload' ) ) {
	function media_handle_upload( $field, $post_id ) {
		return \ArtPulse\Frontend\Tests\OrganizationEventAjaxTest::$media_result; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\wp_insert_post' ) ) {
	function wp_insert_post( $arr ) {
		return 99; }
}
if ( ! function_exists( 'ArtPulse\\Frontend\\set_post_thumbnail' ) ) {
	function set_post_thumbnail( $id, $thumb ) {}
}
if ( ! function_exists( 'ArtPulse\\Frontend\\get_user_meta' ) ) {
	function get_user_meta( $user_id, $key, $single = false ) {
		return 1; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrganizationDashboardShortcode;

/**

 * @group frontend

 */

class OrganizationEventAjaxTest extends TestCase {

	public static array $posts       = array();
	public static array $passed_args = array();
	public static array $updated     = array();
	public static array $json        = array();
	public static $json_error        = null;
	public static array $terms       = array();
	public static $media_result      = 1;

	protected function setUp(): void {
		\ArtPulse\Frontend\StubState::reset();
		self::$posts        = array();
		self::$passed_args  = array();
		self::$updated      = array();
		self::$json         = array();
		self::$json_error   = null;
		self::$terms        = array();
		self::$media_result = 1;
		$_POST              = array();
		$_FILES             = array();
	}

	protected function tearDown(): void {
		$_POST              = array();
		$_FILES             = array();
		self::$posts        = array();
		self::$passed_args  = array();
		self::$updated      = array();
		self::$json         = array();
		self::$json_error   = null;
		self::$terms        = array();
		self::$media_result = 1;
		parent::tearDown();
	}

	public function test_update_event_returns_html(): void {
		self::$posts = array(
			(object) array(
				'ID'         => 7,
				'post_title' => 'First',
			),
			(object) array(
				'ID'         => 8,
				'post_title' => 'Second',
			),
		);

		$addr = array(
			'country' => 'US',
			'state'   => 'CA',
			'city'    => 'LA',
		);

		$_POST = array(
			'nonce'                    => 'n',
			'ap_event_id'              => 7,
			'ap_event_title'           => 'First',
			'ap_event_date'            => '2024-01-01',
			'ap_event_start_date'      => '',
			'ap_event_end_date'        => '',
			'ap_event_location'        => '',
			'ap_venue_name'            => '',
			'ap_event_street_address'  => '',
			'ap_event_country'         => '',
			'ap_event_state'           => '',
			'ap_event_city'            => '',
			'ap_event_postcode'        => '',
			'address_components'       => json_encode( $addr ),
			'ap_event_organizer_name'  => '',
			'ap_event_organizer_email' => '',
		);

		OrganizationDashboardShortcode::handle_ajax_update_event();

		$this->assertSame( 7, self::$updated['ID'] ?? null );
		$this->assertSame( 5, self::$passed_args['meta_value'] ?? null );
		$html = self::$json['updated_list_html'] ?? '';
		$this->assertStringContainsString( 'First', $html );
		$this->assertStringContainsString( 'Second', $html );

		$expected_meta = array( 7, 'address_components', json_encode( $addr ) );
		$this->assertContains( $expected_meta, \ArtPulse\Frontend\StubState::$meta_log );
	}

	public function test_add_event_returns_error_when_upload_fails(): void {
		self::$media_result = new \WP_Error( 'upload_error', 'Upload failed' );
		$_FILES             = array( 'event_banner' => array( 'tmp_name' => 'tmp' ) );

		$_POST = array(
			'nonce'                 => 'n',
			'ap_event_title'        => 'Event',
			'ap_event_date'         => '2024-01-01',
			'ap_event_location'     => '',
			'ap_event_organization' => 1,
		);

		OrganizationDashboardShortcode::handle_ajax_add_event();

		$this->assertSame( 'Upload failed', self::$json_error['message'] ?? null );
	}
}
