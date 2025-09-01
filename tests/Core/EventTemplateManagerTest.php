<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\EventTemplateManager;

/**

 * @group CORE

 */

class EventTemplateManagerTest extends WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Original Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event_id, '_ap_event_date', '2025-01-01' );
		update_post_meta( $this->event_id, '_ap_event_location', 'Location' );
		update_post_meta( $this->event_id, '_ap_event_organization', 99 );
		wp_set_object_terms( $this->event_id, array( 1 ), 'event_type' );
	}

	public function test_duplicate_event_creates_draft_copy(): void {
		$copy = EventTemplateManager::duplicate_event( $this->event_id );
		$post = get_post( $copy );
		$this->assertSame( 'draft', $post->post_status );
		$this->assertSame( 'Original Event (Copy)', $post->post_title );
		$this->assertSame( 'Location', get_post_meta( $copy, '_ap_event_location', true ) );
		$this->assertSame( '99', get_post_meta( $copy, '_ap_event_organization', true ) );
	}

	public function test_template_round_trip(): void {
		$template  = EventTemplateManager::save_as_template( $this->event_id );
		$new_event = EventTemplateManager::create_from_template( $template );
		$this->assertNotEquals( 0, $new_event );
		$this->assertSame( 'draft', get_post_status( $new_event ) );
		$this->assertSame( 'Location', get_post_meta( $new_event, '_ap_event_location', true ) );
		$this->assertSame( '99', get_post_meta( $new_event, '_ap_event_organization', true ) );
	}
}
