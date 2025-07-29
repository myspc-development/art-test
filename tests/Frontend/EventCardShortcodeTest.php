<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventCardShortcode;

class EventCardShortcodeTest extends WP_UnitTestCase {
    public function test_shortcode_outputs_title(): void {
        $id = wp_insert_post([
            'post_title'  => 'Shortcode Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        $html = EventCardShortcode::render(['id' => $id]);
        $this->assertStringContainsString('Shortcode Event', $html);
    }
}
