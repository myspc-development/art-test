<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventChatShortcode;

/**

 * @group FRONTEND

 */

class EventChatShortcodeTest extends WP_UnitTestCase {

        private int $event;
        private int $user;

	public function set_up() {
		parent::set_up();
		$this->event = wp_insert_post(
			array(
				'post_title'  => 'Chat Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		$this->user  = self::factory()->user->create();
	}

        public function test_notice_for_non_rsvp_user(): void {
                wp_set_current_user( $this->user );
                $this->setOutputCallback(static fn() => '');
                ob_start();
                $html   = EventChatShortcode::render( array( 'id' => $this->event ) );
                $output = ob_get_clean();
                $this->assertSame('', $output, 'Unexpected output buffer');
                $this->assertStringContainsString( 'Only attendees can post messages', $html );
                $this->assertStringNotContainsString( 'ap-chat-form', $html );
                $this->assertStringContainsString( 'data-can-post="0"', $html );
        }

        public function test_form_displayed_for_rsvp_user(): void {
                wp_set_current_user( $this->user );
                update_post_meta( $this->event, 'event_rsvp_list', array( $this->user ) );
                $this->setOutputCallback(static fn() => '');
                ob_start();
                $html   = EventChatShortcode::render( array( 'id' => $this->event ) );
                $output = ob_get_clean();
                $this->assertSame('', $output, 'Unexpected output buffer');
                $this->assertStringContainsString( '<form class="ap-chat-form">', $html );
                $this->assertStringContainsString( 'data-can-post="1"', $html );
        }

        public function test_anonymous_prompt_unchanged(): void {
                wp_set_current_user( 0 );
                $this->setOutputCallback(static fn() => '');
                ob_start();
                $html   = EventChatShortcode::render( array( 'id' => $this->event ) );
                $output = ob_get_clean();
                $this->assertSame('', $output, 'Unexpected output buffer');
                $this->assertStringContainsString( 'Please log in to chat.', $html );
        }
}
