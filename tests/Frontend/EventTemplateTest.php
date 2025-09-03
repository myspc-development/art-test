<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';

    function has_post_thumbnail(): bool {
        return false;
    }

    function wp_get_post_terms( $post_id, $taxonomy, $args = [] ) {
        return [];
    }

    function sanitize_email( $email ) {
        return $email;
    }

    function antispambot( $email ) {
        return str_replace( '@', '&#064;', $email );
    }

    function is_wp_error( $thing ) {
        return false;
    }
}

namespace ArtPulse\Frontend\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Tests\Stubs\MockStorage;

/**
 * @group FRONTEND
 */
class EventTemplateTest extends TestCase {
    protected function setUp(): void {
        MockStorage::$post_meta = [];
    }

    public function test_organizer_email_obfuscated(): void {
        MockStorage::$post_meta = [
            '_ap_event_date' => '',
            '_ap_event_venue' => '',
            '_ap_event_address' => '',
            '_ap_event_start_time' => '',
            '_ap_event_end_time' => '',
            '_ap_event_contact' => '',
            '_ap_event_rsvp' => '',
            'event_organizer_email' => 'organizer@example.com',
        ];

        ob_start();
        include __DIR__ . '/../../templates/salient/content-artpulse_event.php';
        $html = ob_get_clean();

        $this->assertStringContainsString( '&#64;', $html );
        $this->assertStringNotContainsString( 'organizer@example.com', $html );
    }
}

}
