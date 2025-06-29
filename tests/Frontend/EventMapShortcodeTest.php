<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventMapShortcode;

class EventMapShortcodeTest extends WP_UnitTestCase
{
    public function test_render_contains_container(): void
    {
        $html = EventMapShortcode::render();
        $this->assertStringContainsString('id="ap-event-map"', $html);
    }
}
