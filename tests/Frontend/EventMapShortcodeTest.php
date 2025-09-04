<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventMapShortcode;

/**

 * @group FRONTEND

 */

class EventMapShortcodeTest extends WP_UnitTestCase {

        public function test_render_contains_container(): void {
                $this->setOutputCallback(static fn() => '');
                ob_start();
                $html   = EventMapShortcode::render();
                $output = ob_get_clean();
                $this->assertSame('', $output, 'Unexpected output buffer');
                $this->assertStringContainsString( 'id="ap-event-map"', $html );
        }
}
