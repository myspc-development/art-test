<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use function ArtPulse\Frontend\ap_filter_events_callback;

/**

 * @group frontend

 */

class EventFilterCallbackTest extends WP_UnitTestCase {

	private int $event1;
	private int $event2;
	private int $cat1;
	private int $cat2;
	private int $tag1;
	private int $tag2;

	public function set_up() {
		parent::set_up();
		add_filter( 'wp_die_handler', array( $this, 'get_die_handler' ) );

		$this->cat1 = wp_create_category( 'Music' );
		$this->cat2 = wp_create_category( 'Art' );
		$this->tag1 = wp_insert_term( 'Indie', 'post_tag' )['term_id'];
		$this->tag2 = wp_insert_term( 'Gallery', 'post_tag' )['term_id'];

		$this->event1 = wp_insert_post(
			array(
				'post_title'  => 'First Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event1, 'venue_name', 'Venue A' );
		update_post_meta( $this->event1, 'event_start_date', '2024-01-10' );
		update_post_meta( $this->event1, 'event_end_date', '2024-01-11' );
		update_post_meta( $this->event1, 'price_type', 'free' );
		update_post_meta( $this->event1, 'event_lat', '34.05' );
		update_post_meta( $this->event1, 'event_lng', '-118.25' );
		wp_set_post_terms( $this->event1, array( $this->cat1 ), 'category' );
		wp_set_post_terms( $this->event1, array( $this->tag1 ), 'post_tag' );

		$this->event2 = wp_insert_post(
			array(
				'post_title'  => 'Second Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event2, 'venue_name', 'Venue B' );
		update_post_meta( $this->event2, 'event_start_date', '2024-08-10' );
		update_post_meta( $this->event2, 'event_end_date', '2024-08-11' );
		update_post_meta( $this->event2, 'price_type', 'paid' );
		update_post_meta( $this->event2, 'event_lat', '40.71' );
		update_post_meta( $this->event2, 'event_lng', '-74.00' );
		wp_set_post_terms( $this->event2, array( $this->cat2 ), 'category' );
		wp_set_post_terms( $this->event2, array( $this->tag2 ), 'post_tag' );
	}

	public function tear_down() {
		remove_filter( 'wp_die_handler', array( $this, 'get_die_handler' ) );
		$_REQUEST = array();
		parent::tear_down();
	}

	public function get_die_handler() {
		return array( $this, 'die_handler' );
	}

	public function die_handler( $message ) {
		// no-op to prevent exiting.
	}

	private function run_callback( array $params ): string {
		$_REQUEST                = $params;
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( 'ap_event_filter_nonce' );
		ob_start();
		ap_filter_events_callback();
		return ob_get_clean();
	}

	public function test_filter_by_keyword(): void {
		$html = $this->run_callback( array( 'keyword' => 'First' ) );
		$this->assertStringContainsString( 'First Event', $html );
		$this->assertStringNotContainsString( 'Second Event', $html );
	}

	public function test_filter_by_venue(): void {
		$html = $this->run_callback( array( 'venue' => 'Venue B' ) );
		$this->assertStringContainsString( 'Second Event', $html );
		$this->assertStringNotContainsString( 'First Event', $html );
	}

	public function test_filter_by_category_and_date(): void {
		$slug = get_term( $this->cat2 )->slug;
		$html = $this->run_callback(
			array(
				'category' => $slug,
				'after'    => '2024-08-01',
				'before'   => '2024-08-31',
			)
		);
		$this->assertStringContainsString( 'Second Event', $html );
		$this->assertStringNotContainsString( 'First Event', $html );
	}

	public function test_category_parameter_sanitized(): void {
		$slug = get_term( $this->cat2 )->slug;
		$html = $this->run_callback(
			array(
				'category' => ' ' . $slug . '%$# ',
			)
		);
		$this->assertStringContainsString( 'Second Event', $html );
		$this->assertStringNotContainsString( 'First Event', $html );
	}

	public function test_filter_by_tag(): void {
		$slug = get_term( $this->tag1 )->slug;
		$html = $this->run_callback(
			array(
				'tags' => $slug,
			)
		);
		$this->assertStringContainsString( 'First Event', $html );
		$this->assertStringNotContainsString( 'Second Event', $html );
	}

	public function test_filter_by_price_type(): void {
		$html = $this->run_callback( array( 'price_type' => 'free' ) );
		$this->assertStringContainsString( 'First Event', $html );
		$this->assertStringNotContainsString( 'Second Event', $html );
	}

	public function test_filter_by_location_radius(): void {
		$html = $this->run_callback(
			array(
				'location' => '34.05,-118.25',
				'radius'   => 0.1,
			)
		);
		$this->assertStringContainsString( 'First Event', $html );
		$this->assertStringNotContainsString( 'Second Event', $html );
	}
}
