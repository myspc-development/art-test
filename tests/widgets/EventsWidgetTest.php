<?php
use ArtPulse\Widgets\EventsWidget;

/**

 * @group widgets

 */

class EventsWidgetTest extends \WP_UnitTestCase {

        private int $user_id;

        protected function setUp(): void {
                parent::setUp();
                $this->user_id = self::factory()->user->create();
                wp_set_current_user( $this->user_id );
        }

        public function test_render_shows_content_for_logged_in_user(): void {
                self::factory()->post->create(
                        array(
                                'post_type'   => 'artpulse_event',
                                'post_title'  => 'Sample Event',
                                'post_status' => 'publish',
                        )
                );

                $html = EventsWidget::render( $this->user_id );
                $this->assertStringContainsString( 'Sample Event', $html );
        }

        public function test_render_shows_placeholder_for_guest(): void {
                wp_set_current_user( 0 );
                $html = EventsWidget::render( 0 );
                $this->assertStringContainsString( 'Please log in', $html );
        }
}
