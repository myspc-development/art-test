<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventCardShortcode;

/**

 * @group FRONTEND

 */

class EventCardShortcodeTest extends WP_UnitTestCase {
	public function test_shortcode_outputs_title(): void {
		$id   = wp_insert_post(
			array(
				'post_title'  => 'Shortcode Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		$html = EventCardShortcode::render( array( 'id' => $id ) );
		$this->assertStringContainsString( 'Shortcode Event', $html );
	}

	public function test_edit_button_shown_for_author(): void {
		$author = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $author );

		$id = wp_insert_post(
			array(
				'post_title'  => 'Author Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $author,
			)
		);

		$html = EventCardShortcode::render( array( 'id' => $id ) );
		$this->assertStringContainsString( 'ap-btn-edit', $html );
	}

	public function test_rsvp_button_shown_for_logged_in_user(): void {
		$author = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$viewer = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$id     = wp_insert_post(
			array(
				'post_title'  => 'Viewer Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $author,
			)
		);

		wp_set_current_user( $viewer );
		$html = EventCardShortcode::render( array( 'id' => $id ) );
		$this->assertStringContainsString( 'ap-rsvp-btn', $html );
		$this->assertStringNotContainsString( 'ap-btn-edit', $html );
	}
}
